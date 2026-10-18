<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\{Activity, MailConfig};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Mail};
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    const TYPES = ['quotation' => ['Quotation', 'Q-'], 'invoice' => ['Invoice', 'INV-']];
    const STATUS = ['quotation' => ['draft', 'sent', 'accepted', 'cancelled'], 'invoice' => ['draft', 'sent', 'paid', 'cancelled']];

    private function doc($id)
    {
        $d = DB::table('documents')->where('id', $id)->first();
        abort_unless($d, 404);
        return $d;
    }
    private function items($id)
    {
        return DB::table('document_items')->where('document_id', $id)->orderBy('sort')->orderBy('id')->get();
    }
    public static function totals($d, $items): array
    {
        $sub = round($items->sum(fn($i) => $i->qty * $i->price), 2);
        $after = max(0, $sub - (float) $d->discount);
        $tax = round($after * (float) $d->tax_percent / 100, 2);
        return ['sub' => $sub, 'discount' => (float) $d->discount, 'tax' => $tax, 'total' => round($after + $tax, 2)];
    }
    private function nextNumber(string $type): string
    {
        $max = DB::table('documents')->where('type', $type)->pluck('number')->map(fn($n) => (int) preg_replace('/\D/', '', $n))->max() ?? 0;
        return self::TYPES[$type][1] . str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
    }

    public function index(Request $r)
    {
        $q = DB::table('documents')
            ->leftJoinSub(DB::table('document_items')->selectRaw('document_id, sum(qty * price) as sub')->groupBy('document_id'), 'i', 'i.document_id', '=', 'documents.id')
            ->select('documents.*', 'i.sub');
        $type = isset(self::TYPES[$r->query('type')]) ? $r->query('type') : null;
        if ($type) $q->where('documents.type', $type);
        return view('admin.documents', ['items' => $q->orderByDesc('documents.id')->paginate(30)->withQueryString(), 'type' => $type]);
    }

    public function form(Request $r, $id = null)
    {
        $s = Setting::map();
        if ($id) {
            $d = $this->doc($id);
            $items = $this->items($id);
        } else {
            $type = isset(self::TYPES[$r->query('type')]) ? $r->query('type') : 'quotation';
            $d = (object) [
                'id' => null,
                'type' => $type,
                'number' => null,
                'lead_id' => null,
                'client_name' => '',
                'client_email' => '',
                'client_phone' => '',
                'client_address' => '',
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays($type === 'quotation' ? 14 : 7)->toDateString(),
                'currency' => $s['default_currency'] ?? 'BDT',
                'tax_percent' => $s['default_tax'] ?? 0,
                'discount' => 0,
                'notes' => '',
                'terms' => $s['default_terms'] ?? '',
                'status' => 'draft',
            ];
            $items = collect([(object) ['description' => '', 'qty' => 1, 'price' => 0]]);
            if ($lid = (int) $r->query('lead')) {
                $m = DB::table('messages')->where('id', $lid)->first();
                if ($m) {
                    $d->lead_id = $m->id;
                    $d->client_name = $m->name;
                    $d->client_email = $m->email;
                    $d->client_phone = (string) $m->phone;
                    $items = collect([(object) ['description' => $m->kind === 'quote' ? (string) $m->service : (string) $m->subject, 'qty' => 1, 'price' => 0]]);
                }
            }
        }
        return view('admin.document-form', ['d' => $d, 'items' => $items, 'types' => self::TYPES, 'statuses' => self::STATUS[$d->type]]);
    }

    public function save(Request $r, $id = null)
    {
        $existing = $id ? $this->doc($id) : null;
        $v = $r->validate([
            'type' => $existing ? 'nullable' : ['required', Rule::in(array_keys(self::TYPES))],
            'lead_id' => 'nullable|integer',
            'client_name' => 'required|max:150',
            'client_email' => 'nullable|email|max:150',
            'client_phone' => 'nullable|max:40',
            'client_address' => 'nullable|max:500',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'currency' => 'required|max:8',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0|max:999999999999',
            'notes' => 'nullable|max:3000',
            'terms' => 'nullable|max:3000',
            'items' => 'required|array|min:1|max:50',
            'items.*.description' => 'required|max:300',
            'items.*.qty' => 'required|numeric|min:0.01|max:1000000',
            'items.*.price' => 'required|numeric|min:0|max:999999999',
        ]);
        $type = $existing->type ?? $v['type'];
        $row = [
            'lead_id' => $v['lead_id'] ?? null,
            'client_name' => $v['client_name'],
            'client_email' => $v['client_email'] ?? null,
            'client_phone' => $v['client_phone'] ?? null,
            'client_address' => $v['client_address'] ?? null,
            'issue_date' => $v['issue_date'],
            'due_date' => $v['due_date'] ?? null,
            'currency' => strtoupper($v['currency']),
            'tax_percent' => (float) ($v['tax_percent'] ?? 0),
            'discount' => (float) ($v['discount'] ?? 0),
            'notes' => $v['notes'] ?? null,
            'terms' => $v['terms'] ?? null,
            'updated_at' => now(),
        ];
        if ($existing) {
            DB::table('documents')->where('id', $id)->update($row);
            $number = $existing->number;
        } else {
            for ($try = 0; $try < 3; $try++) {
                try {
                    $number = $this->nextNumber($type);
                    $id = DB::table('documents')->insertGetId($row + ['type' => $type, 'number' => $number, 'status' => 'draft', 'created_by' => auth()->id(), 'created_at' => now()]);
                    break;
                } catch (\Illuminate\Database\QueryException $e) {
                    if ($try === 2) throw $e;
                }
            }
        }
        DB::table('document_items')->where('document_id', $id)->delete();
        foreach (array_values($v['items']) as $i => $it) {
            DB::table('document_items')->insert(['document_id' => $id, 'description' => $it['description'], 'qty' => $it['qty'], 'price' => $it['price'], 'sort' => $i]);
        }
        $label = self::TYPES[$type][0];
        Activity::note($existing ? 'updated' : 'created', ($existing ? 'Updated ' : 'Created ') . "$label $number for {$v['client_name']}", 'Documents', $id);
        return redirect("/admin/documents/$id/edit")->with('ok', "$label $number saved.");
    }

    private function logo(array $s): ?string
    {
        $p = $s['logo'] ?? null;
        $cands = $p ? [preg_replace('/\.webp$/i', '.jpg', $p), $p] : [];
        $cands[] = 'logo.png';
        foreach ($cands as $c) {
            $f = public_path($c);
            if (is_file($f) && preg_match('/\.(png|jpe?g|gif)$/i', $f)) {
                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                return 'data:image/' . ($ext === 'jpg' ? 'jpeg' : $ext) . ';base64,' . base64_encode(file_get_contents($f));
            }
        }
        return null;
    }
    private function pdfData($d): string
    {
        abort_unless(class_exists(\Dompdf\Dompdf::class), 500, 'The PDF library is not installed. Run: composer require dompdf/dompdf');
        $items = $this->items($d->id);
        $s = Setting::map();
        $html = view('documents.pdf', ['d' => $d, 'items' => $items, 't' => self::totals($d, $items), 's' => $s, 'logo' => $this->logo($s), 'label' => self::TYPES[$d->type][0]])->render();
        $pdf = new \Dompdf\Dompdf(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => false]);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4');
        $pdf->render();
        return $pdf->output();
    }
    public function pdf($id)
    {
        $d = $this->doc($id);
        return response($this->pdfData($d), 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="' . $d->number . '.pdf"']);
    }

    public function send(Request $r, $id)
    {
        $d = $this->doc($id);
        $v = $r->validate(['to' => 'required|email|max:150', 'message' => 'nullable|max:2000']);
        $s = Setting::map();
        if (empty($s['mail_host'])) return back()->with('err', 'Set up the SMTP email in Profile & Settings first.');
        try {
            $pdf = $this->pdfData($d);
            MailConfig::apply($s);
            $label = self::TYPES[$d->type][0];
            $site = $s['name'] ?? config('app.name');
            $text = trim($v['message'] ?? '') ?: "Hello {$d->client_name},\n\nPlease find the attached $label {$d->number}.\n\nIf you have any question, just reply to this email.";
            Mail::raw($text . "\n\n" . $site, function ($m) use ($v, $d, $pdf, $s, $label, $site) {
                $m->to($v['to'])->subject("$label {$d->number} from $site")->attachData($pdf, $d->number . '.pdf', ['mime' => 'application/pdf']);
                if (!empty($s['email'])) $m->replyTo($s['email']);
            });
        } catch (\Throwable $e) {
            report($e);
            return back()->with('err', 'Could not send: ' . $e->getMessage());
        }
        if ($d->status === 'draft') DB::table('documents')->where('id', $id)->update(['status' => 'sent']);
        Activity::note('updated', "Emailed {$d->number} to {$v['to']}", 'Documents', $id);
        return back()->with('ok', "Emailed to {$v['to']}.");
    }

    private function copy($d, string $type)
    {
        $new = (array) $d;
        unset($new['id']);
        $new = array_merge($new, ['type' => $type, 'status' => 'draft', 'issue_date' => now()->toDateString(), 'due_date' => null, 'from_id' => $type !== $d->type ? $d->id : null, 'created_by' => auth()->id(), 'created_at' => now(), 'updated_at' => now()]);
        for ($try = 0; $try < 3; $try++) {
            try {
                $new['number'] = $this->nextNumber($type);
                $nid = DB::table('documents')->insertGetId($new);
                break;
            } catch (\Illuminate\Database\QueryException $e) {
                if ($try === 2) throw $e;
            }
        }
        foreach ($this->items($d->id) as $i) {
            DB::table('document_items')->insert(['document_id' => $nid, 'description' => $i->description, 'qty' => $i->qty, 'price' => $i->price, 'sort' => $i->sort]);
        }
        Activity::note('created', "Created {$new['number']} from {$d->number}", 'Documents', $nid);
        return redirect("/admin/documents/$nid/edit")->with('ok', "{$new['number']} created. Check the details and save.");
    }
    public function convert($id)
    {
        $d = $this->doc($id);
        abort_unless($d->type === 'quotation', 404);
        return $this->copy($d, 'invoice');
    }
    public function duplicate($id)
    {
        $d = $this->doc($id);
        return $this->copy($d, $d->type);
    }
    public function status(Request $r, $id)
    {
        $d = $this->doc($id);
        $r->validate(['status' => ['required', Rule::in(self::STATUS[$d->type])]]);
        DB::table('documents')->where('id', $id)->update(['status' => $r->input('status'), 'updated_at' => now()]);
        Activity::note('updated', "{$d->number} marked as {$r->input('status')}", 'Documents', $id);
        return back()->with('ok', 'Status updated.');
    }
    public function destroy($id)
    {
        $d = $this->doc($id);
        DB::table('document_items')->where('document_id', $id)->delete();
        DB::table('documents')->where('id', $id)->delete();
        Activity::note('deleted', "Deleted {$d->number}", 'Documents', $id);
        return redirect('/admin/documents')->with('ok', "{$d->number} deleted.");
    }
}

<?php

namespace App\Http\Controllers;

use App\Support\{Activity, Redirects};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RedirectController extends Controller
{
    private function make(string $fromRaw, string $toRaw, int $code): ?string
    {
        $from = Redirects::norm($fromRaw);
        $toRaw = trim($toRaw);
        $external = (bool) preg_match('#^https?://#i', $toRaw);
        if ($external && strtolower((string) parse_url($toRaw, PHP_URL_HOST)) === strtolower(request()->getHost())) $external = false;
        if ($external && !preg_match('#^https://\S+$#i', $toRaw)) return 'Addresses on other websites must start with https://';
        if ($external && !auth()->user()->hasPerm('settings')) return 'Only an Admin can redirect to another website.';
        $to = $external ? mb_substr($toRaw, 0, 400) : Redirects::norm($toRaw);
        if ($from === '/' || str_starts_with($from, '/admin')) return 'That address cannot be redirected.';
        if (!$external && $to === $from) return 'The old and the new address are the same.';
        if (!$external && DB::table('redirects')->where('from_path', $to)->where('to_url', $from)->exists()) return 'This would create a redirect loop.';
        DB::table('redirects')->updateOrInsert(['from_path' => $from], ['to_url' => $to, 'code' => $code, 'auto' => 0, 'created_at' => now(), 'updated_at' => now()]);
        Activity::note('created', "Redirect $from to $to", 'Redirects');
        return null;
    }

    public function index(Request $r)
    {
        $tab = in_array($r->query('tab'), ['redirects', 'errors', 'bots', 'ignored']) ? $r->query('tab') : 'redirects';
        $log = fn() => DB::table('not_found_log');
        $counts = [
            'redirects' => DB::table('redirects')->count(),
            'errors' => $log()->where('is_bot', 0)->where('ignored', 0)->count(),
            'bots' => $log()->where('is_bot', 1)->where('ignored', 0)->count(),
            'ignored' => $log()->where('ignored', 1)->count(),
        ];
        if ($tab === 'redirects') {
            $q = DB::table('redirects');
            if ($s = trim((string) $r->query('q'))) {
                $like = '%' . addcslashes($s, '%_\\') . '%';
                $q->where(fn($x) => $x->where('from_path', 'like', $like)->orWhere('to_url', 'like', $like));
            }
            $items = $q->orderByDesc('id')->paginate(40)->withQueryString();
        } else {
            $q = $log();
            $q = match ($tab) {
                'errors' => $q->where('is_bot', 0)->where('ignored', 0),
                'bots' => $q->where('is_bot', 1)->where('ignored', 0),
                default => $q->where('ignored', 1),
            };
            $items = $q->orderByDesc('hits')->orderByDesc('last_seen')->paginate(40)->withQueryString();
        }
        return view('admin.redirects', compact('tab', 'counts', 'items'));
    }
    public function store(Request $r)
    {
        $d = $r->validate(['from' => 'required|max:300', 'to' => 'required|max:400', 'code' => 'required|in:301,302']);
        $err = $this->make($d['from'], $d['to'], (int) $d['code']);
        return $err ? back()->withErrors(['from' => $err])->withInput() : back()->with('ok', 'Redirect saved.');
    }
    public function destroy($id)
    {
        DB::table('redirects')->where('id', $id)->delete();
        return back()->with('ok', 'Redirect deleted.');
    }
    public function fix(Request $r, $id)
    {
        $row = DB::table('not_found_log')->where('id', $id)->first();
        abort_unless($row, 404);
        $d = $r->validate(['to' => 'required|max:400']);
        $err = $this->make($row->path, $d['to'], 301);
        if ($err) return back()->with('err', $err);
        DB::table('not_found_log')->where('id', $id)->delete();
        return back()->with('ok', 'Redirect created. Visitors who use the old address now go to the new one.');
    }
    public function ignore($id)
    {
        DB::table('not_found_log')->where('id', $id)->update(['ignored' => 1]);
        return back()->with('ok', 'Ignored.');
    }
    public function restore($id)
    {
        DB::table('not_found_log')->where('id', $id)->update(['ignored' => 0]);
        return back()->with('ok', 'Moved back to the list.');
    }
    public function forget($id)
    {
        DB::table('not_found_log')->where('id', $id)->delete();
        return back()->with('ok', 'Removed from the list.');
    }
    public function clearBots()
    {
        DB::table('not_found_log')->where('is_bot', 1)->delete();
        return back()->with('ok', 'Bot and noise entries cleared.');
    }
}

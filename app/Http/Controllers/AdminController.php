<?php

namespace App\Http\Controllers;

use App\Models\{Entry, Setting};
use App\Support\{Html, Img, MailConfig};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Crypt, Hash, Mail};
use Illuminate\Support\Str;

class AdminController extends Controller
{
    const G = [
        'Profile' => [
            'name' => ['input', 'nullable|max:200'],
            'role' => ['input', 'nullable|max:500'],
            'intro' => ['textarea', 'nullable|max:800'],
            'about' => ['textarea', 'nullable'],
            'email' => ['input', 'nullable|email'],
            'phone' => ['input', 'nullable|max:40'],
            'linkedin' => ['input', 'nullable|url'],
            'github' => ['input', 'nullable|url'],
            'facebook' => ['input', 'nullable|url'],
            'photo' => ['file', 'nullable|image|mimes:jpg,jpeg,png,webp,gif,svg,bmp,ico|max:8192'],
            'logo' => ['file', 'nullable|image|mimes:jpg,jpeg,png,webp,gif,svg,bmp,ico|max:8192'],
            'favicon' => ['file', 'nullable|image|mimes:jpg,jpeg,png,webp,gif,svg,bmp,ico|max:8192'],
            'cv' => ['file', 'nullable|mimes:pdf,doc,docx|max:5120']
        ],
        'SEO' => [
            'seo_title' => ['input', 'nullable|max:120'],
            'seo_description' => ['textarea', 'nullable|max:170']
        ],
        'Analytics' => ['ga_id' => ['input', 'nullable|regex:/^G-[A-Z0-9]+$/'], 'gsc_verify' => ['input', 'nullable|regex:/^[A-Za-z0-9_-]+$/']],
        'Email (SMTP)' => [
            'mail_host' => ['input', 'nullable|max:150'],
            'mail_port' => ['input', 'nullable|integer'],
            'mail_encryption' => ['select', 'nullable|in:tls,ssl,none', ['tls', 'ssl', 'none']],
            'mail_username' => ['input', 'nullable|max:150'],
            'mail_password' => ['password', 'nullable|max:200'],
            'mail_from' => ['input', 'nullable|email']
        ],
    ];
    private function flat(): array
    {
        return array_merge(...array_values(self::G));
    }
    private function R(string $t): array
    {
        $R = config("admin.$t");
        abort_unless($R, 404);
        return $R;
    }
    private function q(string $t)
    {
        return Entry::of($t)->newQuery();
    }
    private function prep(Request $r, array $f): array
    {
        $d = $r->validate(array_map(fn($x) => $x[1], $f));
        foreach ($f as $n => $x) {
            if ($x[0] == 'file') {
                if ($r->hasFile($n)) $d[$n] = str_contains($x[1], 'image') ? Img::store($r->file($n), $n == 'photo' ? 900 : 1600) : 'storage/' . $r->file($n)->store('uploads', 'public');
                else unset($d[$n]);
            } elseif ($x[0] == 'check') $d[$n] = $r->boolean($n);
            elseif ($x[0] == 'rich') $d[$n] = Html::clean((string) ($d[$n] ?? ''));
            elseif ($x[0] == 'password') {
                if (($d[$n] ?? '') === '') unset($d[$n]);
                else $d[$n] = Crypt::encryptString($d[$n]);
            }
        }
        return $d;
    }
    public function loginForm()
    {
        return view('admin.login');
    }
    public function login(Request $r)
    {
        if (Auth::attempt($r->validate(['email' => 'required|email', 'password' => 'required']))) {
            $r->session()->regenerate();
            return redirect('/admin');
        }
        return back()->withErrors(['email' => 'Invalid login.']);
    }
    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect('/');
    }
    public function passwordForm()
    {
        return view('admin.password');
    }
    public function passwordSave(Request $r)
    {
        $r->validate(['current_password' => 'required', 'password' => 'required|min:10|confirmed|different:current_password']);
        $u = $r->user();
        if (!Hash::check($r->current_password, $u->password)) return back()->withErrors(['current_password' => 'Current password is wrong.']);
        $u->password = Hash::make($r->password);
        $u->save();
        return back()->with('ok', 'Password changed.');
    }
    public function dashboard()
    {
        $c = fn($t) => $this->q($t);
        return view('admin.dashboard', [
            's' => Setting::map(),
            'counts' => collect(config('admin'))->map(fn($R, $k) => $c($k)->count()),
            'n' => ['unread' => $c('messages')->where('is_read', 0)->count(), 'messages' => $c('messages')->count(), 'drafts' => $c('posts')->where('published', 0)->count()],
            'msgs' => $c('messages')->latest()->limit(5)->get(),
            'recent' => $c('posts')->latest()->limit(5)->get()
        ]);
    }
    public function settings()
    {
        return view('admin.settings', ['s' => Setting::map(), 'g' => self::G]);
    }
    public function settingsSave(Request $r)
    {
        foreach ($this->prep($r, $this->flat()) as $k => $v) Setting::updateOrCreate(['name' => $k], ['value' => $v]);
        return back()->with('ok', 'Saved.');
    }
    public function testMail()
    {
        $s = Setting::map();
        if (empty($s['mail_host']) || empty($s['email'])) return back()->with('err', 'Save the SMTP host and your contact email first.');
        MailConfig::apply($s);
        try {
            Mail::raw('SMTP test from your portfolio admin panel.', fn($m) => $m->to($s['email'])->subject('SMTP test'));
            return back()->with('ok', 'Test email sent to ' . $s['email']);
        } catch (\Throwable $e) {
            return back()->with('err', 'Failed: ' . $e->getMessage());
        }
    }
    public function upload(Request $r)
    {
        $r->validate(['image' => 'required|image|max:8192']);
        return response()->json(['url' => '/' . Img::store($r->file('image'))]);
    }
    public function messages()
    {
        return view('admin.messages', ['items' => $this->q('messages')->latest()->get()]);
    }
    public function read($id)
    {
        $this->q('messages')->findOrFail($id)->update(['is_read' => 1]);
        return back();
    }
    public function index($t)
    {
        return view('admin.list', ['t' => $t, 'R' => $this->R($t), 'items' => $this->q($t)->orderBy('sort')->orderByDesc('id')->get()]);
    }
    public function form($t, $id = null)
    {
        return view('admin.form', ['t' => $t, 'R' => $this->R($t), 'item' => $id ? $this->q($t)->findOrFail($id) : null]);
    }
    public function save(Request $r, $t, $id = null)
    {
        $R = $this->R($t);
        $d = $this->prep($r, $R['f']);
        if (array_key_exists('sort', $R['f'])) $d['sort'] = (int) ($d['sort'] ?? 0);
        if (array_key_exists('slug', $R['f'])) {
            $s = Str::slug(($d['slug'] ?? '') ?: $d['title']);
            while ($this->q($t)->where('slug', $s)->where('id', '!=', $id ?? 0)->exists()) $s .= '-' . rand(10, 99);
            $d['slug'] = $s;
        }
        $id ? $this->q($t)->findOrFail($id)->update($d) : $this->q($t)->create($d);
        return redirect("/admin/$t")->with('ok', 'Saved.');
    }
    public function destroy($t, $id)
    {
        $this->q($t)->findOrFail($id)->delete();
        return back()->with('ok', 'Deleted.');
    }
}

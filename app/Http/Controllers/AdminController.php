<?php

namespace App\Http\Controllers;

use App\Models\{Entry, Setting, User};
use App\Support\{Html, Img, MailConfig, Totp};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Crypt, DB, Hash, Mail, Schema, Storage};
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    const IMG = 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:8192';
    const G = [
        'Profile' => [
            'name' => ['input', 'nullable|max:200'],
            'role' => ['input', 'nullable|max:500'],
            'intro' => ['richs', 'nullable|max:3000'],
            'about' => ['rich', 'nullable'],
            'email' => ['input', 'nullable|email'],
            'phone' => ['input', 'nullable|max:40'],
            'whatsapp' => ['input', 'nullable|regex:/^\+?[0-9 ()-]{8,20}$/'],
            'linkedin' => ['input', 'nullable|url'],
            'github' => ['input', 'nullable|url'],
            'facebook' => ['input', 'nullable|url'],
            'photo' => ['file', self::IMG],
            'logo' => ['file', self::IMG],
            'favicon' => ['file', self::IMG],
            'cv' => ['file', 'nullable|mimes:pdf,doc,docx|max:5120'],
        ],
        'SEO' => ['seo_title' => ['input', 'nullable|max:120'], 'seo_description' => ['textarea', 'nullable|max:170']],
        'Analytics' => ['ga_id' => ['input', 'nullable|regex:/^G-[A-Z0-9]+$/'], 'gsc_verify' => ['input', 'nullable|regex:/^[A-Za-z0-9_-]+$/']],
        'Legal' => ['privacy_policy' => ['rich', 'nullable'], 'affiliate_disclosure' => ['rich', 'nullable']],
        'Affiliate' => ['affiliate_domains' => ['textarea', 'nullable|max:1000']],
        'Maintenance' => ['maintenance' => ['check', 'nullable'], 'maintenance_message' => ['textarea', 'nullable|max:300']],
        'Email (SMTP)' => [
            'mail_host' => ['input', 'nullable|max:150'],
            'mail_port' => ['input', 'nullable|integer'],
            'mail_encryption' => ['select', 'nullable|in:tls,ssl,none', ['tls', 'ssl', 'none']],
            'mail_username' => ['input', 'nullable|max:150'],
            'mail_password' => ['password', 'nullable|max:200'],
            'mail_from' => ['input', 'nullable|email'],
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
    private function allow(string $t): void
    {
        $perm = $t === 'posts' ? 'posts.own' : ($t === 'messages' ? 'messages' : 'content');
        abort_unless(auth()->user()->hasPerm($perm), 403);
    }
    private function mine(string $t, $id): void
    {
        if ($t !== 'posts' || auth()->user()->hasPerm('posts.all')) return;
        abort_unless($this->q('posts')->where('id', $id)->where('user_id', auth()->id())->exists(), 403);
    }
    private function prep(Request $r, array $f): array
    {
        $d = $r->validate(array_map(fn($x) => $x[1], $f));
        foreach ($f as $n => $x) {
            if ($x[0] == 'file') {
                if ($r->hasFile($n)) $d[$n] = str_contains($x[1], 'image') ? Img::store($r->file($n), ['photo' => 900, 'logo' => 600, 'favicon' => 256][$n] ?? 1600) : 'storage/' . $r->file($n)->store('uploads', 'public');
                elseif (($pick = (string) $r->input($n . '_pick')) !== '' && preg_match('#^storage/uploads/[A-Za-z0-9]{8,64}\.webp$#', $pick) && is_file(public_path($pick))) $d[$n] = $pick;
                else unset($d[$n]);
            } elseif ($x[0] == 'check') $d[$n] = $r->boolean($n);
            elseif (in_array($x[0], ['rich', 'richs'])) $d[$n] = Html::clean((string) ($d[$n] ?? ''));
            elseif ($x[0] == 'password') {
                if (($d[$n] ?? '') === '') unset($d[$n]);
                else $d[$n] = Crypt::encryptString($d[$n]);
            }
        }
        return $d;
    }

    // ---------- login, password, 2FA ----------
    public function loginForm()
    {
        return view('admin.login');
    }
    public function login(Request $r)
    {
        $c = $r->validate(['email' => 'required|email', 'password' => 'required']);
        if (!Auth::validate($c)) {
            \App\Support\Activity::note('failed', 'Failed login for ' . $c['email'], null, null, (object) ['id' => null, 'name' => 'Visitor']);
            return back()->withErrors(['email' => 'Invalid login.']);
        }
        $u = User::where('email', $c['email'])->first();
        if (!$u->hasPerm('dashboard')) return back()->withErrors(['email' => 'This account does not have access to the admin panel.']);
        if ($u->two_factor_enabled) {
            $r->session()->put('2fa', ['id' => $u->id, 'at' => now()->timestamp]);
            $r->session()->forget('2fa_fail');
            return redirect('/admin/two-factor');
        }
        Auth::login($u);
        $r->session()->regenerate();
        return redirect('/admin');
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
    private function dec(?string $v): ?string
    {
        try {
            return $v ? Crypt::decryptString($v) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
    private function makeCodes(): array
    {
        return array_map(function () {
            $h = bin2hex(random_bytes(5));
            return substr($h, 0, 5) . '-' . substr($h, 5);
        }, range(1, 8));
    }
    private function saveCodes(Request $r, array $codes): void
    {
        $hashes = array_map(fn($c) => Hash::make(str_replace('-', '', $c)), $codes);
        $r->user()->forceFill(['two_factor_recovery' => Crypt::encryptString(json_encode($hashes))])->save();
        $r->session()->put('new_codes', $codes);
    }
    public function twoFactorForm(Request $r)
    {
        if (!$r->session()->has('2fa')) return redirect('/admin/login');
        return view('admin.two-factor');
    }
    public function twoFactorCheck(Request $r)
    {
        $p = $r->session()->get('2fa');
        if (!$p || now()->timestamp - $p['at'] > 300) {
            $r->session()->forget(['2fa', '2fa_fail']);
            return redirect('/admin/login')->withErrors(['email' => 'Login expired. Please sign in again.']);
        }
        $r->validate(['code' => 'required|string|max:20']);
        $u = User::find($p['id']);
        if (!$u || !$u->hasPerm('dashboard')) {
            $r->session()->forget(['2fa', '2fa_fail']);
            return redirect('/admin/login')->withErrors(['email' => 'This account does not have access to the admin panel.']);
        }
        $in = trim((string) $r->input('code'));
        $ok = false;
        $usedRecovery = false;
        $secret = $this->dec($u->two_factor_secret);
        if ($secret) {
            $step = Totp::verify($secret, $in, (int) $u->two_factor_last);
            if ($step !== null) {
                $u->forceFill(['two_factor_last' => $step])->save();
                $ok = true;
            } else {
                $codes = json_decode($this->dec($u->two_factor_recovery) ?? '[]', true) ?: [];
                $norm = strtolower(str_replace([' ', '-'], '', $in));
                foreach ($codes as $i => $h) {
                    if (Hash::check($norm, $h)) {
                        unset($codes[$i]);
                        $u->forceFill(['two_factor_recovery' => Crypt::encryptString(json_encode(array_values($codes)))])->save();
                        $ok = $usedRecovery = true;
                        break;
                    }
                }
            }
        }
        if (!$ok) {
            $n = (int) $r->session()->get('2fa_fail', 0) + 1;
            $r->session()->put('2fa_fail', $n);
            if ($n >= 5) {
                $r->session()->forget(['2fa', '2fa_fail']);
                return redirect('/admin/login')->withErrors(['email' => 'Too many wrong codes. Please sign in again.']);
            }
            return back()->withErrors(['code' => 'Wrong code. Please try again.']);
        }
        Auth::login($u);
        $r->session()->regenerate();
        $r->session()->forget(['2fa', '2fa_fail']);
        return redirect('/admin')->with('ok', $usedRecovery ? 'You used a recovery code. Please create new recovery codes in Two-factor (2FA).' : 'Welcome back.');
    }
    public function security(Request $r)
    {
        $u = $r->user();
        $setup = null;
        if (!$u->two_factor_enabled) {
            $secret = $r->session()->get('2fa_setup');
            if (!$secret) {
                $secret = Totp::secret();
                $r->session()->put('2fa_setup', $secret);
            }
            $setup = ['secret' => $secret, 'uri' => Totp::uri($secret, $u->email, Setting::map()['name'] ?? config('app.name'))];
        }
        $left = count(json_decode($this->dec($u->two_factor_recovery) ?? '[]', true) ?: []);
        return view('admin.security', [
            'enabled' => (bool) $u->two_factor_enabled,
            'setup' => $setup,
            'left' => $left,
            'newCodes' => $r->session()->pull('new_codes'),
            'alerts' => (bool) $u->login_alerts,
            'smtp' => !empty(Setting::map()['mail_host']),
            'devices' => DB::table('login_devices')->where('user_id', $u->id)->orderByDesc('last_seen_at')->limit(8)->get(),
        ]);
    }
    public function securityEnable(Request $r)
    {
        $r->validate(['code' => 'required']);
        $secret = $r->session()->get('2fa_setup');
        $step = $secret ? Totp::verify($secret, (string) $r->input('code')) : null;
        if ($step === null) return back()->withErrors(['code' => 'That code is not correct. Check the code in your app and your computer clock, then try again.']);
        $r->user()->forceFill(['two_factor_secret' => Crypt::encryptString($secret), 'two_factor_enabled' => true, 'two_factor_last' => $step])->save();
        $this->saveCodes($r, $this->makeCodes());
        $r->session()->forget('2fa_setup');
        return redirect('/admin/security')->with('ok', 'Two-factor login is now ON.');
    }
    public function securityRecovery(Request $r)
    {
        $r->validate(['password' => 'required']);
        if (!Hash::check($r->password, $r->user()->password)) return back()->withErrors(['password' => 'Wrong password.']);
        $this->saveCodes($r, $this->makeCodes());
        return redirect('/admin/security')->with('ok', 'New recovery codes created. The old codes no longer work.');
    }
    public function securityDisable(Request $r)
    {
        $r->validate(['password' => 'required']);
        if (!Hash::check($r->password, $r->user()->password)) return back()->withErrors(['password' => 'Wrong password.']);
        $r->user()->forceFill(['two_factor_secret' => null, 'two_factor_enabled' => false, 'two_factor_recovery' => null, 'two_factor_last' => null])->save();
        return redirect('/admin/security')->with('ok', 'Two-factor login is now OFF.');
    }

    // ---------- dashboard ----------
    public function dashboard()
    {
        if (!auth()->user()->hasPerm('content')) return redirect('/admin/posts');
        $c = fn($t) => $this->q($t);
        try {
            $traffic = $this->traffic();
        } catch (\Throwable $e) {
            $traffic = null;
        }
        return view('admin.dashboard', [
            's' => Setting::map(),
            'counts' => collect(config('admin'))->map(fn($R, $k) => $c($k)->count()),
            'n' => ['unread' => $c('messages')->where('is_read', 0)->count(), 'messages' => $c('messages')->count(), 'drafts' => $c('posts')->where('published', 0)->count()],
            'msgs' => $c('messages')->latest()->limit(5)->get(),
            'recent' => $c('posts')->latest()->limit(5)->get(),
            'traffic' => $traffic,
        ]);
    }
    private function traffic(): array
    {
        $pv = fn() => DB::table('page_views');
        $since = fn(int $d) => now()->subDays($d - 1)->toDateString();
        $uniq = fn(string $from) => DB::query()->fromSub(DB::table('page_views')->select('day', 'visitor')->where('day', '>=', $from)->groupBy('day', 'visitor'), 'x')->count();
        $cards = [];
        foreach ([['Today', 1], ['Last 7 days', 7], ['Last 30 days', 30]] as [$label, $d]) {
            $f = $since($d);
            $cards[] = ['label' => $label, 'views' => $pv()->where('day', '>=', $f)->count(), 'visitors' => $uniq($f)];
        }
        $f14 = $since(14);
        $views = $pv()->where('day', '>=', $f14)->selectRaw('day, count(*) as c')->groupBy('day')->pluck('c', 'day');
        $vis = $pv()->where('day', '>=', $f14)->selectRaw('day, count(distinct visitor) as c')->groupBy('day')->pluck('c', 'day');
        $series = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $k = $d->toDateString();
            $series[] = ['label' => $d->format('d M'), 'short' => $d->format('j'), 'views' => (int) ($views[$k] ?? 0), 'visitors' => (int) ($vis[$k] ?? 0)];
        }
        $f30 = $since(30);
        return [
            'cards' => $cards,
            'series' => $series,
            'total30' => $cards[2]['views'],
            'pages' => $pv()->where('day', '>=', $f30)->selectRaw('path, count(*) as views, count(distinct visitor) as visitors')->groupBy('path')->orderByDesc('views')->limit(8)->get(),
            'refs' => $pv()->where('day', '>=', $f30)->whereNotNull('referrer')->selectRaw('referrer, count(*) as views')->groupBy('referrer')->orderByDesc('views')->limit(6)->get(),
            'direct' => $pv()->where('day', '>=', $f30)->whereNull('referrer')->count(),
            'devices' => $pv()->where('day', '>=', $f30)->selectRaw('device, count(*) as views')->groupBy('device')->orderByDesc('views')->get(),
        ];
    }

    // ---------- settings ----------
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

    // ---------- messages and newsletter ----------
    public function messages()
    {
        return view('admin.messages', ['items' => $this->q('messages')->latest()->get()]);
    }
    public function read($id)
    {
        $this->q('messages')->findOrFail($id)->update(['is_read' => 1]);
        return back();
    }
    public function subscribers()
    {
        $items = $this->q('subscribers')->latest()->get();
        return view('admin.subscribers', ['items' => $items, 'active' => $items->where('status', 'active')->count()]);
    }
    public function subscribersExport()
    {
        $safe = fn($v) => preg_match('/^[=+\-@\t\r]/', (string) $v) ? "'" . $v : $v;
        return response()->streamDownload(function () use ($safe) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['email', 'status', 'subscribed_at', 'unsubscribe_url']);
            foreach ($this->q('subscribers')->orderBy('id')->get() as $r) {
                fputcsv($out, [$safe($r->email), $r->status, $r->created_at, url('/unsubscribe/' . $r->token)]);
            }
            fclose($out);
        }, 'subscribers-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
    public function subscriberDelete($id)
    {
        $this->q('subscribers')->findOrFail($id)->delete();
        return back()->with('ok', 'Subscriber deleted.');
    }

    // ---------- media library ----------
    public function media()
    {
        return view('admin.media', ['items' => $this->mediaItems()]);
    }
    public function mediaUpload(Request $r)
    {
        $r->validate(['images' => 'required|array|max:20', 'images.*' => 'image|max:8192']);
        foreach ($r->file('images') as $f) Img::store($f, 1600);
        \App\Support\Activity::note('created', 'Uploaded ' . count($r->file('images')) . ' image(s)', 'Media');
        return back()->with('ok', count($r->file('images')) . ' image(s) uploaded.');
    }
    public function mediaDelete(string $name)
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{8,64}\.(webp|jpe?g|png|gif|ico)$/i', $name), 404);
        $this->mediaRemove($name);
        \App\Support\Activity::note('deleted', 'Deleted image: ' . $name, 'Media');
        return back()->with('ok', 'Image deleted.');
    }
    public function mediaCleanup()
    {
        $n = 0;
        $limit = now()->subDay()->timestamp;
        foreach ($this->mediaItems() as $m) {
            if (!$m['used'] && $m['time'] < $limit) {
                $this->mediaRemove($m['name']);
                $n++;
            }
        }
        return back()->with('ok', "$n unused image(s) deleted.");
    }
    public function mediaPicker()
    {
        $disk = Storage::disk('public');
        $out = [];
        foreach ($disk->files('uploads') as $f) {
            if (!preg_match('/^uploads\/([A-Za-z0-9]{8,64})\.webp$/', $f, $m)) continue;
            $thumb = $disk->exists("uploads/{$m[1]}-240.webp") ? "uploads/{$m[1]}-240.webp" : $f;
            $out[] = ['path' => 'storage/' . $f, 'thumb' => '/storage/' . $thumb, 'time' => $disk->lastModified($f)];
        }
        usort($out, fn($a, $b) => $b['time'] <=> $a['time']);
        return response()->json($out);
    }
    private function mediaRemove(string $name): void
    {
        $disk = Storage::disk('public');
        $base = pathinfo($name, PATHINFO_FILENAME);
        $disk->delete('uploads/' . $name);
        if (str_ends_with(strtolower($name), '.webp')) {
            foreach (['jpg', 'avif'] as $e) $disk->delete("uploads/$base.$e");
            foreach ([240, 480, 800, 1200] as $w) foreach (['webp', 'avif'] as $e) $disk->delete("uploads/$base-$w.$e");
        }
    }
    private function mediaHay(): array
    {
        $h = [];
        $add = function (string $label, string $t, array $cols) use (&$h) {
            if (!Schema::hasTable($t)) return;
            $s = '';
            foreach (DB::table($t)->get($cols) as $row) foreach ($cols as $c) $s .= ' ' . ($row->$c ?? '');
            $h[$label] = ($h[$label] ?? '') . $s;
        };
        $add('Profile & Settings', 'settings', ['value']);
        $add('Projects', 'projects', ['image', 'details']);
        $add('Blog posts', 'posts', ['image', 'body']);
        $add('Services', 'services', ['details']);
        $add('Testimonials', 'testimonials', ['photo']);
        return $h;
    }
    private function mediaItems(): array
    {
        $disk = Storage::disk('public');
        $files = collect($disk->files('uploads'))->filter(fn($f) => preg_match('/\.(webp|jpe?g|png|gif|ico)$/i', $f) && !preg_match('/-\d+\.webp$/i', $f));
        $webp = $files->filter(fn($f) => str_ends_with(strtolower($f), '.webp'))->map(fn($f) => pathinfo($f, PATHINFO_FILENAME))->all();
        $hay = $this->mediaHay();
        $items = [];
        foreach ($files as $f) {
            $base = pathinfo($f, PATHINFO_FILENAME);
            if (preg_match('/\.jpe?g$/i', $f) && in_array($base, $webp)) continue;
            $items[] = [
                'name' => basename($f),
                'url' => '/storage/' . $f,
                'size' => $disk->size($f),
                'time' => $disk->lastModified($f),
                'used' => array_keys(array_filter($hay, fn($s) => str_contains($s, $base))),
            ];
        }
        usort($items, fn($a, $b) => $b['time'] <=> $a['time']);
        return $items;
    }

    // ---------- backup ----------
    public function backupForm()
    {
        return view('admin.backup', ['last' => Setting::map()['last_backup'] ?? null]);
    }
    public function backupDownload()
    {
        abort_unless(class_exists(\ZipArchive::class), 500, 'The PHP zip extension is not enabled.');
        $pdo = DB::connection()->getPdo();
        $tables = array_merge(['users', 'settings', 'messages', 'subscribers'], array_keys(config('admin')));
        $sql = '-- Portfolio backup ' . now()->toDateTimeString() . "\nSET FOREIGN_KEY_CHECKS=0;\n";
        foreach ($tables as $name) {
            if (!Schema::hasTable($name)) continue;
            foreach (DB::table($name)->get() as $row) {
                $row = (array) $row;
                $cols = implode(',', array_map(fn($c) => "`$c`", array_keys($row)));
                $vals = implode(',', array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), $row));
                $sql .= "INSERT INTO `$name` ($cols) VALUES ($vals);\n";
            }
        }
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $tmp = tempnam(sys_get_temp_dir(), 'bk');
        $zip = new \ZipArchive;
        $zip->open($tmp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('database.sql', $sql);
        $dir = storage_path('app/public');
        if (is_dir($dir)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $f) {
                if ($f->isFile()) $zip->addFile($f->getPathname(), 'uploads/' . ltrim(str_replace('\\', '/', substr($f->getPathname(), strlen($dir))), '/'));
            }
        }
        $zip->close();
        Setting::updateOrCreate(['name' => 'last_backup'], ['value' => now()->format('d M Y, H:i')]);
        \App\Support\Activity::note('backup', 'Downloaded a backup', 'Backup');
        return response()->download($tmp, 'portfolio-backup-' . now()->format('Y-m-d-His') . '.zip')->deleteFileAfterSend(true);
    }

    // ---------- content sections (projects, posts, services...) ----------
    public function index($t)
    {
        $this->allow($t);
        $q = $this->q($t)->orderBy('sort')->orderByDesc('id');
        if ($t === 'posts' && !auth()->user()->hasPerm('posts.all')) $q->where('user_id', auth()->id());
        return view('admin.list', ['t' => $t, 'R' => $this->R($t), 'items' => $q->get()]);
    }
    public function form($t, $id = null)
    {
        $this->allow($t);
        if ($id) $this->mine($t, $id);
        return view('admin.form', ['t' => $t, 'R' => $this->R($t), 'item' => $id ? $this->q($t)->findOrFail($id) : null]);
    }
    public function save(Request $r, $t, $id = null)
    {
        $this->allow($t);
        if ($id) $this->mine($t, $id);
        $R = $this->R($t);
        $d = $this->prep($r, $R['f']);
        if (array_key_exists('sort', $R['f'])) $d['sort'] = (int) ($d['sort'] ?? 0);
        if (!$id && array_key_exists('sort', $R['f']) && $d['sort'] === 0) {
            $d['sort'] = $t === 'projects' ? ((int) $this->q($t)->min('sort') - 1) : ((int) $this->q($t)->max('sort') + 1);
        }
        if (array_key_exists('slug', $R['f'])) {
            $s = Str::slug(($d['slug'] ?? '') ?: $d['title']);
            while ($this->q($t)->where('slug', $s)->where('id', '!=', $id ?? 0)->exists()) $s .= '-' . rand(10, 99);
            $d['slug'] = $s;
        }
        if ($t === 'posts' && !$id) $d['user_id'] = auth()->id();
        $id ? $this->q($t)->findOrFail($id)->update($d) : $this->q($t)->create($d);
        return redirect("/admin/$t")->with('ok', 'Saved.');
    }
    public function destroy($t, $id)
    {
        $this->allow($t);
        $this->mine($t, $id);
        $this->q($t)->findOrFail($id)->delete();
        return back()->with('ok', 'Deleted.');
    }
    public function reorder(Request $r, $t)
    {
        $this->allow($t);
        $R = $this->R($t);
        abort_unless(array_key_exists('sort', $R['f']), 404);
        foreach (array_map('intval', (array) $r->input('ids', [])) as $i => $id) {
            $this->q($t)->where('id', $id)->update(['sort' => $i]);
        }
        return response()->json(['ok' => true]);
    }

    // ---------- users and roles ----------
    private function rank(string $role): int
    {
        return config('roles.rank')[$role] ?? 0;
    }
    private function canManage(User $target): bool
    {
        $me = auth()->user();
        return $me->role === 'super_admin' || $this->rank($target->role) < $this->rank($me->role);
    }
    private function assignable(): array
    {
        $me = auth()->user();
        return collect(config('roles.labels'))->filter(fn($l, $k) => $me->role === 'super_admin' || $this->rank($k) < $this->rank($me->role))->all();
    }
    private function otherSuper(User $u): bool
    {
        return User::where('role', 'super_admin')->where('active', 1)->where('id', '!=', $u->id)->exists();
    }
    public function users()
    {
        return view('admin.users', ['items' => User::orderBy('id')->get()]);
    }
    public function userForm($id = null)
    {
        $u = $id ? User::findOrFail($id) : null;
        if ($u) abort_unless($this->canManage($u), 403);
        return view('admin.user-form', ['u' => $u, 'roles' => $this->assignable()]);
    }
    public function userSave(Request $r, $id = null)
    {
        $u = $id ? User::findOrFail($id) : null;
        if ($u) abort_unless($this->canManage($u), 403);
        $self = $u && $u->id === auth()->id();
        $d = $r->validate([
            'name' => 'required|max:100',
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($id)],
            'role' => $self ? ['nullable'] : ['required', Rule::in(array_keys($this->assignable()))],
            'password' => [$u ? 'nullable' : 'required', 'min:10', 'max:100'],
        ]);
        $active = $r->boolean('active');
        if ($self) {
            $d['role'] = $u->role;
            $active = true;
        }
        if ($u && $u->role === 'super_admin' && ($d['role'] !== 'super_admin' || !$active) && !$this->otherSuper($u)) {
            return back()->withErrors(['role' => 'There must be at least one active super admin.'])->withInput();
        }
        $row = ['name' => $d['name'], 'email' => $d['email'], 'role' => $d['role'], 'active' => $active];
        if (!empty($d['password'])) $row['password'] = Hash::make($d['password']);
        if ($u && $r->boolean('reset_2fa')) $row += ['two_factor_enabled' => false, 'two_factor_secret' => null, 'two_factor_recovery' => null, 'two_factor_last' => null];
        if ($u) {
            if (!empty($d['password']) || !$active || $d['role'] !== $u->role) {
                DB::table('sessions')->where('user_id', $u->id)->where('id', '!=', session()->getId())->delete();
            }
            $u->forceFill($row)->save();
        } else {
            (new User)->forceFill($row)->save();
        }
        return redirect('/admin/users')->with('ok', 'User saved.');
    }
    public function userDelete($id)
    {
        $u = User::findOrFail($id);
        abort_unless($this->canManage($u) && $u->id !== auth()->id(), 403);
        if ($u->role === 'super_admin' && !$this->otherSuper($u)) return back()->with('err', 'There must be at least one active super admin.');
        DB::table('sessions')->where('user_id', $u->id)->delete();
        $this->q('posts')->where('user_id', $u->id)->update(['user_id' => null]);
        DB::table('login_devices')->where('user_id', $u->id)->delete();
        $u->delete();
        return back()->with('ok', 'User deleted.');
    }

    public function activity(Request $r)
    {
        $q = DB::table('activity_logs')->orderByDesc('id');
        if ($u = $r->query('user')) $q->where('user_name', $u);
        if ($a = $r->query('action')) $q->where('action', $a);
        return view('admin.activity', [
            'items' => $q->paginate(40)->withQueryString(),
            'users' => DB::table('activity_logs')->distinct()->orderBy('user_name')->pluck('user_name'),
            'actions' => ['created', 'updated', 'deleted', 'login', 'logout', 'failed', 'backup'],
        ]);
    }

    public function securityAlerts(Request $r)
    {
        $on = $r->boolean('login_alerts');
        $r->user()->forceFill(['login_alerts' => $on])->save();
        return back()->with('ok', $on ? 'Login alert emails are ON.' : 'Login alert emails are OFF.');
    }
}

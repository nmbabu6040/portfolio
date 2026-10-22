<?php

namespace App\Http\Controllers;

use App\Models\{Setting, User};
use App\Support\{Backup, Captcha};
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    private function row(string $st, string $label, string $detail = '', string $fix = ''): array
    {
        return compact('st', 'label', 'detail', 'fix');
    }
    private function errors(): array
    {
        $f = storage_path('logs/laravel.log');
        if (!is_file($f) || !($fh = @fopen($f, 'r'))) return [0, []];
        fseek($fh, max(0, filesize($f) - 400000));
        $txt = (string) stream_get_contents($fh);
        fclose($fh);
        preg_match_all('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] \w+\.(?:ERROR|CRITICAL|ALERT|EMERGENCY): ([^\n]{0,220})/m', $txt, $m, PREG_SET_ORDER);
        $rows = array_map(fn($x) => ['time' => $x[1], 'msg' => $x[2]], $m);
        $day = now()->subDay()->format('Y-m-d H:i:s');
        return [count(array_filter($rows, fn($x) => $x['time'] >= $day)), array_slice(array_reverse($rows), 0, 8)];
    }

    public function index()
    {
        $s = Setting::map();
        $r = fn(...$a) => $this->row(...$a);
        $prod = app()->environment('production');
        $sections = [];

        $x = [];
        $x[] = version_compare(PHP_VERSION, '8.2', '>=') ? $r('ok', 'PHP version', PHP_VERSION) : $r('bad', 'PHP version', PHP_VERSION, 'Laravel 12 needs PHP 8.2 or newer.');
        foreach (
            [
                'pdo_mysql' => 'the database',
                'mbstring' => 'text handling',
                'openssl' => 'encryption',
                'fileinfo' => 'file uploads',
                'dom' => 'cleaning rich text',
                'curl' => 'web requests (CAPTCHA, email)',
                'gd' => 'image processing'
            ] as $e => $why
        ) {
            $x[] = extension_loaded($e) ? $r('ok', "PHP extension: $e") : $r('bad', "PHP extension missing: $e", "Needed for $why.", "Enable extension=$e in php.ini, or ask your hosting.");
        }
        foreach (
            [
                'zip' => 'backups and PDF files',
                'exif' => 'fixing the rotation of phone photos'
            ] as $e => $why
        ) {
            $x[] = extension_loaded($e) ? $r('ok', "PHP extension: $e") : $r('warn', "PHP extension missing: $e", "Used for $why.", "Enable extension=$e in php.ini.");
        }
        $x[] = function_exists('imagewebp') ? $r('ok', 'WebP images', 'Uploads are converted to WebP.') : $r('warn', 'WebP is not supported', 'Images are saved as they were uploaded.', 'Use a PHP build whose GD supports WebP.');
        $x[] = function_exists('imageavif') ? $r('ok', 'AVIF images', 'AVIF copies are created.') : $r('info', 'AVIF is not supported', 'Only WebP sizes are created. This is fine.');
        $x[] = $r('info', 'Upload limits', 'upload_max_filesize ' . ini_get('upload_max_filesize') . ', post_max_size ' . ini_get('post_max_size') . ', memory_limit ' . ini_get('memory_limit'));

        $sections['Server'] = $x;

        $x = [];
        $debug = (bool) config('app.debug');
        $x[] = $debug && $prod ? $r('bad', 'Debug mode is ON', 'Visitors can see error details and secrets.', 'Set APP_DEBUG=false in .env, then run php artisan config:clear.')
            : ($debug ? $r('info', 'Debug mode is ON', 'Fine on your own computer. Turn it off before you go live.') : $r('ok', 'Debug mode is OFF'));
        $x[] = $prod ? $r('ok', 'Environment: production') : $r('info', 'Environment: ' . app()->environment(), 'Set APP_ENV=production when the site is live.');
        $x[] = config('app.key') ? $r('ok', 'App key is set') : $r('bad', 'App key is missing', '', 'Run php artisan key:generate');
        $host = request()->getSchemeAndHttpHost();
        $x[] = rtrim((string) config('app.url'), '/') === $host ? $r('ok', 'APP_URL matches this address', $host)
            : $r('warn', 'APP_URL does not match this address', 'APP_URL is ' . config('app.url') . ' but you are visiting ' . $host . '.', 'Set APP_URL in .env to your real address.');
        $x[] = request()->isSecure() ? $r('ok', 'HTTPS is on')
            : ($prod ? $r('bad', 'HTTPS is off', 'Logins and forms are not encrypted.', 'Turn on SSL (AutoSSL in cPanel) and force HTTPS.') : $r('info', 'HTTPS is off', 'Fine on your own computer.'));
        $x[] = config('app.timezone') === 'UTC' ? $r('warn', 'Timezone is UTC', 'Times in the admin and the weekly backup use UTC.', 'Set APP_TIMEZONE=Asia/Dhaka in .env.') : $r('ok', 'Timezone', config('app.timezone'));
        $x[] = app()->configurationIsCached() ? $r('ok', 'Config is cached')
            : ($prod ? $r('warn', 'Config is not cached', 'The site is a little slower.', 'Run php artisan optimize on the server.') : $r('info', 'Config is not cached', 'Normal while you build the site.'));

        $x[] = config('session.secure') ? $r('ok', 'Session cookie is HTTPS-only') : ($prod ? $r('warn', 'Session cookie is not HTTPS-only', '', 'Set SESSION_SECURE_COOKIE=true in .env once HTTPS works.') : $r('info', 'Session cookie is not HTTPS-only', 'Fine on your own computer.'));
        $x[] = (int) config('session.lifetime') <= 60 ? $r('ok', 'Admin sessions expire after ' . config('session.lifetime') . ' minutes of inactivity') : $r('info', 'Sessions last ' . config('session.lifetime') . ' minutes', '', 'A shorter time is safer. Set SESSION_LIFETIME=60 in .env.');
        $x[] = config('session.encrypt') ? $r('ok', 'Session data is encrypted') : $r('info', 'Session data is not encrypted', '', 'Set SESSION_ENCRYPT=true in .env for extra safety.');
        $x[] = config('security.csp') === 'enforce' ? $r('ok', 'Security headers and CSP are active') : $r('warn', 'CSP mode is ' . config('security.csp'), '', 'Set CSP_MODE=enforce in .env.');

        $sections['Environment'] = $x;

        $x = [];
        try {
            DB::select('select 1');
            $x[] = $r('ok', 'Database connection');
        } catch (\Throwable $e) {
            $x[] = $r('bad', 'Database connection failed', $e->getMessage());
        }
        try {
            $done = DB::table('migrations')->pluck('migration')->all();
            $files = array_map(fn($f) => basename($f, '.php'), glob(database_path('migrations/*.php')) ?: []);
            $pending = count(array_diff($files, $done));
            $x[] = $pending ? $r('bad', "$pending migration(s) have not run", '', 'Run php artisan migrate') : $r('ok', 'All migrations have run');
        } catch (\Throwable $e) {
        }
        $sections['Database'] = $x;

        $x = [];
        foreach ([storage_path() => 'storage', storage_path('app/public') => 'storage/app/public', base_path('bootstrap/cache') => 'bootstrap/cache'] as $p => $n) {
            $x[] = is_writable($p) ? $r('ok', "$n is writable") : $r('bad', "$n is not writable", '', 'Give the web server write permission (chmod 775).');
        }
        $x[] = is_link(public_path('storage')) || is_dir(public_path('storage')) ? $r('ok', 'public/storage link exists') : $r('bad', 'public/storage link is missing', 'Uploaded images will not show.', 'Run php artisan storage:link');
        $free = @disk_free_space(storage_path());
        if ($free !== false) $x[] = $free < 500 * 1048576 ? $r('warn', 'Low disk space', Backup::human((int) $free) . ' free', 'Free some space or delete old backups.') : $r('ok', 'Disk space', Backup::human((int) $free) . ' free');
        $sections['Storage'] = $x;

        $x = [];
        $x[] = !empty($s['mail_host']) ? $r('ok', 'SMTP email is set', $s['mail_host']) : $r('warn', 'SMTP email is not set', 'Messages are saved, but no email is sent to you.', 'Fill in Email (SMTP) in Profile & Settings and press Send test email.');
        $x[] = !empty($s['email']) ? $r('ok', 'Contact email is set', $s['email']) : $r('warn', 'Contact email is not set', '', 'Set it in Profile & Settings.');
        $x[] = Captcha::enabled() ? $r('ok', 'CAPTCHA (Turnstile) is on') : $r('info', 'CAPTCHA is off', 'Forms use a hidden field and a time check. Add Turnstile keys for stronger protection.');
        $sections['Email and forms'] = $x;

        $x = [];
        $hb = $s['scheduler_heartbeat'] ?? null;
        $alive = $hb && \Carbon\Carbon::parse($hb)->gt(now()->subMinutes(12));
        $x[] = $alive ? $r('ok', 'Scheduler is running', 'Last seen ' . \Carbon\Carbon::parse($hb)->diffForHumans())
            : $r(!empty($s['auto_backup']) ? 'bad' : 'warn', 'Scheduler is not running', $hb ? 'Last seen ' . \Carbon\Carbon::parse($hb)->diffForHumans() : 'Never seen.', 'The weekly backup needs it. Add the cron job (live site) or run php artisan schedule:work (your computer). See Admin > Backup.');
        $x[] = !empty($s['auto_backup']) ? $r('ok', 'Weekly backup is ON') : $r('warn', 'Weekly backup is OFF', '', 'Switch it on in Admin > Backup.');
        $files = Backup::list();
        $x[] = !$files ? $r('warn', 'No backup on the server yet', '', 'Click Run backup now in Admin > Backup.')
            : ($files[0]['time'] < now()->subDays(10)->timestamp ? $r('warn', 'The last server backup is old', date('d M Y, H:i', $files[0]['time']), 'Run a backup now and check the scheduler.') : $r('ok', 'Recent server backup', date('d M Y, H:i', $files[0]['time'])));
        $sections['Backups'] = $x;

        $x = [];
        $staff = User::whereIn('role', config('roles.perms.dashboard'))->where('active', 1)->get();
        $no2fa = $staff->filter(fn($u) => !$u->two_factor_enabled);
        $x[] = $no2fa->isEmpty() ? $r('ok', 'Everyone with admin access uses 2FA')
            : $r($no2fa->whereIn('role', ['super_admin', 'admin'])->count() ? 'warn' : 'info', $no2fa->count() . ' user(s) without 2FA', $no2fa->pluck('email')->take(5)->implode(', '), 'Ask them to turn on Two-factor (2FA) in the Account menu.');
        $x[] = User::where('email', 'admin@example.com')->exists() ? $r('bad', 'The default admin email (admin@example.com) still exists', '', 'Change the email in Users & roles and use a strong password.') : $r('ok', 'The default admin email is not used');
        $x[] = !empty($s['maintenance']) ? $r('warn', 'Maintenance mode is ON', 'Visitors see the maintenance page.', 'Switch it off in Profile & Settings when you are ready.') : $r('ok', 'Maintenance mode is off');
        $sections['Security'] = $x;

        $x = [];
        foreach ([['seo_title', 'SEO title'], ['seo_description', 'SEO description'], ['logo', 'Logo'], ['favicon', 'Favicon']] as [$k, $l]) {
            $x[] = !empty($s[$k]) ? $r('ok', "$l is set") : $r('warn', "$l is not set", '', 'Add it in Profile & Settings.');
        }
        $x[] = !empty($s['ga_id']) ? $r('ok', 'Google Analytics is connected') : $r('info', 'Google Analytics is not connected', 'Add it after you have a domain.');
        $x[] = !empty($s['gsc_verify']) ? $r('ok', 'Search Console is verified') : $r('info', 'Search Console is not set', 'Add it after you have a domain.');
        $x[] = $r('info', 'Sitemap address', url('/sitemap.xml'));
        $sections['SEO'] = $x;

        [$errCount, $errList] = $this->errors();
        $all = collect($sections)->flatten(1);
        return view('admin.health', [
            'sections' => $sections,
            'bad' => $all->where('st', 'bad')->count(),
            'warn' => $all->where('st', 'warn')->count(),
            'errCount' => $errCount,
            'errList' => $errList,
        ]);
    }
}

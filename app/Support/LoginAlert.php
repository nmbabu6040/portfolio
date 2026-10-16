<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\{Cookie, DB, Mail};
use Illuminate\Support\Str;

class LoginAlert
{
    public static function handle($user): void
    {
        try {
            $req = request();
            $ua = (string) $req->userAgent();
            $ip = (string) $req->ip();
            $cookie = (string) $req->cookie('dev_id', '');
            $token = $cookie !== '' ? $cookie : Str::random(40);
            $hash = hash('sha256', $token);
            Cookie::queue(cookie('dev_id', $token, 60 * 24 * 365, '/', null, $req->isSecure(), true, false, 'Lax'));
            $row = DB::table('login_devices')->where('user_id', $user->id)->where('token_hash', $hash)->first();
            if ($row) {
                DB::table('login_devices')->where('id', $row->id)->update(['last_seen_at' => now(), 'ip' => $ip, 'user_agent' => mb_substr($ua, 0, 255)]);
                Activity::note('login', 'Logged in', null, null, $user);
                return;
            }
            $first = !DB::table('login_devices')->where('user_id', $user->id)->exists();
            DB::table('login_devices')->insert(['user_id' => $user->id, 'token_hash' => $hash, 'user_agent' => mb_substr($ua, 0, 255), 'ip' => $ip, 'created_at' => now(), 'last_seen_at' => now()]);
            $sent = false;
            if (!$first && $user->login_alerts) $sent = self::mail($user, $ip, $ua);
            $note = $first ? ' (first device recorded)' : ' from a new device' . ($sent ? ', alert email sent' : ($user->login_alerts ? ', alert email NOT sent (check the SMTP settings)' : ', login alerts are off'));
            Activity::note('login', 'Logged in' . $note, null, null, $user);
        } catch (\Throwable $e) {
            report($e);
        }
    }
    private static function mail($user, string $ip, string $ua): bool
    {
        $s = Setting::map();
        if (empty($s['mail_host'])) return false;
        MailConfig::apply($s);
        config(['mail.mailers.smtp.timeout' => 8]);
        $site = $s['name'] ?? config('app.name');
        $body = "Hello {$user->name},\n\nYour admin account on {$site} was just used to log in from a new device.\n\n"
            . 'Time: ' . now()->format('d M Y, H:i') . ' (' . config('app.timezone') . ")\n"
            . 'Device: ' . self::describe($ua) . "\n"
            . "IP address: $ip\n\n"
            . "If this was you, you can ignore this email.\n\n"
            . "If it was NOT you:\n"
            . '1. Change your password now: ' . url('/admin/password') . "\n"
            . '2. Turn on two-factor login: ' . url('/admin/security') . "\n"
            . "3. Ask a super admin to check the Activity log.\n";
        try {
            Mail::raw($body, fn($m) => $m->to($user->email)->subject("New login to your $site admin account"));
            return true;
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }
    public static function describe(?string $ua): string
    {
        $ua = (string) $ua;
        $b = 'Unknown browser';
        foreach (['Edg' => 'Edge', 'OPR' => 'Opera', 'Firefox' => 'Firefox', 'Chrome' => 'Chrome', 'Safari' => 'Safari'] as $k => $n) {
            if (stripos($ua, $k) !== false) {
                $b = $n;
                break;
            }
        }
        $o = 'Unknown system';
        foreach (['Windows' => 'Windows', 'Android' => 'Android', 'iPhone' => 'iOS', 'iPad' => 'iOS', 'Mac OS' => 'macOS', 'Linux' => 'Linux'] as $k => $n) {
            if (stripos($ua, $k) !== false) {
                $o = $n;
                break;
            }
        }
        return "$b on $o";
    }
}

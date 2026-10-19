<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Crypt, Http};

class Captcha
{
    public static function enabled(): bool
    {
        $s = Setting::map();
        return !empty($s['turnstile_site']) && !empty($s['turnstile_secret']);
    }
    public static function ok(Request $r): bool
    {
        $s = Setting::map();
        if (empty($s['turnstile_site']) || empty($s['turnstile_secret'])) return true;
        $token = (string) $r->input('cf-turnstile-response');
        if ($token === '') return false;
        try {
            $secret = Crypt::decryptString($s['turnstile_secret']);
        } catch (\Throwable $e) {
            return true;
        }
        try {
            $res = Http::asForm()->timeout(6)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', ['secret' => $secret, 'response' => $token, 'remoteip' => $r->ip()]);
            return $res->failed() ? true : (bool) $res->json('success');
        } catch (\Throwable $e) {
            report($e);
            return true;
        }
    }
}

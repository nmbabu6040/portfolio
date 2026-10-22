<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $r, Closure $next)
    {
        $res = $next($r);
        $h = $res->headers;
        $h->set('X-Content-Type-Options', 'nosniff');
        $h->set('X-Frame-Options', 'SAMEORIGIN');
        $h->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $h->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        $h->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
        if ($r->isSecure() && !app()->environment('local')) $h->set('Strict-Transport-Security', 'max-age=31536000');
        if ($r->is('admin') || $r->is('admin/*')) $h->set('Cache-Control', 'no-store, private');
        $mode = config('security.csp', 'enforce');
        if ($mode !== 'off' && str_starts_with((string) $h->get('Content-Type'), 'text/html')) {
            $h->set($mode === 'report' ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy', $this->csp());
        }
        return $res;
    }
    private function csp(): string
    {
        $book = '';
        try {
            $u = Setting::map()['booking_url'] ?? '';
            if ($u && ($host = parse_url($u, PHP_URL_HOST))) $book = ' https://' . $host;
        } catch (\Throwable $e) {
        }
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://challenges.cloudflare.com https://www.googletagmanager.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com",
            "font-src 'self' data: https://cdn.jsdelivr.net https://fonts.gstatic.com",
            "img-src 'self' data: blob: https:",
            "connect-src 'self' https://*.google-analytics.com https://*.analytics.google.com https://*.googletagmanager.com https://challenges.cloudflare.com",
            'frame-src https://challenges.cloudflare.com https://www.youtube-nocookie.com https://player.vimeo.com' . $book,
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ]);
    }
}

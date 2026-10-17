<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrackVisit
{
    public function handle(Request $r, Closure $next)
    {
        $res = $next($r);
        try {
            $ua = (string) $r->userAgent();
            if (
                $r->isMethod('GET') && $res->getStatusCode() === 200 && $ua !== '' && !$r->ajax() && !$r->user()
                && !preg_match('/bot|crawl|spider|slurp|facebookexternalhit|preview|monitor|uptime|curl|wget|python|httpclient|headless|lighthouse|pingdom|gtmetrix/i', $ua)
            ) {
                $ref = parse_url((string) $r->headers->get('referer'), PHP_URL_HOST);
                $ref = $ref ? preg_replace('/^www\./', '', strtolower($ref)) : null;
                if ($ref && $ref === preg_replace('/^www\./', '', strtolower($r->getHost()))) $ref = null;
                $day = now()->toDateString();
                $path = mb_substr('/' . ltrim($r->path(), '/'), 0, 190);
                DB::table('page_views')->insert([
                    'day' => $day,
                    'path' => $path,
                    'visitor' => substr(hash('sha256', $r->ip() . '|' . $ua . '|' . $day . '|' . config('app.key')), 0, 32),
                    'referrer' => $ref ? mb_substr($ref, 0, 100) : null,
                    'device' => preg_match('/tablet|ipad/i', $ua) ? 'tablet' : (preg_match('/mobile|android|iphone/i', $ua) ? 'mobile' : 'desktop'),
                    'created_at' => now(),
                ]);
                // remember where this visitor came from (first visit of the session), used when they send a message
                if (!$r->session()->has('lead_src')) {
                    $r->session()->put('lead_src', [
                        'label' => $this->label($ref, $r),
                        'landing' => $path,
                        'campaign' => mb_substr(trim((string) $r->query('utm_campaign')), 0, 100) ?: null,
                        'referrer' => $ref ? mb_substr($ref, 0, 100) : null,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // never break the site because of statistics
        }
        return $res;
    }
    private function label(?string $ref, Request $r): string
    {
        if ($u = trim((string) $r->query('utm_source'))) {
            $m = trim((string) $r->query('utm_medium'));
            return mb_substr(ucfirst($u) . ($m !== '' ? " / $m" : ''), 0, 60);
        }
        if (!$ref) return 'Direct';
        foreach (['google' => 'Google', 'bing' => 'Bing', 'duckduckgo' => 'DuckDuckGo', 'facebook' => 'Facebook', 'fb.com' => 'Facebook', 'linkedin' => 'LinkedIn', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'whatsapp' => 'WhatsApp', 'twitter' => 'X', 't.co' => 'X', 'x.com' => 'X'] as $k => $n) {
            if (str_contains($ref, $k)) return $n;
        }
        return mb_substr($ref, 0, 60);
    }
}

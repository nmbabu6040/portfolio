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
                DB::table('page_views')->insert([
                    'day' => $day,
                    'path' => mb_substr('/' . ltrim($r->path(), '/'), 0, 190),
                    'visitor' => substr(hash('sha256', $r->ip() . '|' . $ua . '|' . $day . '|' . config('app.key')), 0, 32),
                    'referrer' => $ref ? mb_substr($ref, 0, 100) : null,
                    'device' => preg_match('/tablet|ipad/i', $ua) ? 'tablet' : (preg_match('/mobile|android|iphone/i', $ua) ? 'mobile' : 'desktop'),
                    'created_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            // never break the site because of statistics
        }
        return $res;
    }
}

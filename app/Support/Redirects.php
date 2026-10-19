<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Redirects
{
    // "https://site.com/Old-Page/?x=1" and "old-page/" both become "/Old-Page"
    public static function norm(string $p): string
    {
        $p = trim($p);
        $p = preg_replace('#^https?://[^/]+#i', '', $p);
        $p = preg_replace('/[?#].*$/', '', $p);
        $p = '/' . ltrim(preg_replace('#/+#', '/', $p), '/');
        if ($p !== '/') $p = rtrim($p, '/');
        return mb_substr($p, 0, 190);
    }
    // when a post or service gets a new slug, the old address redirects to the new one
    public static function slugChanged(string $prefix, string $old, string $new): void
    {
        try {
            $from = $prefix . $old;
            $to = $prefix . $new;
            if ($from === $to) return;
            DB::table('redirects')->where('from_path', $to)->delete();
            DB::table('redirects')->where('to_url', $from)->update(['to_url' => $to, 'updated_at' => now()]);
            DB::table('redirects')->updateOrInsert(['from_path' => $from], ['to_url' => $to, 'code' => 301, 'auto' => 1, 'created_at' => now(), 'updated_at' => now()]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
    // called for every page-not-found: redirects if a rule exists, otherwise records the broken link. Returns null to show the normal 404 page.
    public static function handle404(Request $r)
    {
        try {
            if (!in_array($r->method(), ['GET', 'HEAD']) || $r->expectsJson() || $r->ajax()) return null;
            $path = self::norm($r->path());
            if (str_starts_with($path, '/admin')) return null;
            $row = DB::table('redirects')->where('from_path', $path)->first();
            if ($row) {
                DB::table('redirects')->where('id', $row->id)->update(['hits' => DB::raw('hits + 1'), 'last_hit_at' => now()]);
                $to = $row->to_url;
                $qs = $r->getQueryString();
                if ($qs && !str_contains($to, '?')) $to .= '?' . $qs;
                return str_starts_with($to, 'http') ? redirect()->away($to, (int) $row->code) : redirect($to, (int) $row->code);
            }
            self::log($path, $r);
        } catch (\Throwable $e) {
            report($e);
        }
        return null;
    }
    private static function log(string $path, Request $r): void
    {
        $ref = parse_url((string) $r->headers->get('referer'), PHP_URL_HOST);
        $ref = $ref ? mb_substr(preg_replace('/^www\./', '', strtolower($ref)), 0, 100) : null;
        $row = DB::table('not_found_log')->where('path', $path)->first();
        if ($row) {
            DB::table('not_found_log')->where('id', $row->id)->update(['hits' => DB::raw('hits + 1'), 'last_seen' => now(), 'referrer' => $ref ?: $row->referrer]);
            return;
        }
        if (DB::table('not_found_log')->count() >= 5000) return;
        DB::table('not_found_log')->insert(['path' => $path, 'hits' => 1, 'referrer' => $ref, 'is_bot' => self::scanner($path) ? 1 : 0, 'ignored' => 0, 'first_seen' => now(), 'last_seen' => now()]);
        if (random_int(1, 50) === 1) DB::table('not_found_log')->where('last_seen', '<', now()->subDays(180))->delete();
    }
    // addresses that only robots and browsers ask for: they are not your broken links
    private static function scanner(string $p): bool
    {
        return (bool) preg_match('#(wp-(admin|login|content|includes|json)|xmlrpc|phpmyadmin|phpinfo|\.env|\.git|\.svn|\.ht|/vendor/|cgi-bin|/shell|/setup|favicon\.ico|apple-touch-icon[^/]*\.png|browserconfig\.xml|\.(php|asp|aspx|jsp|cgi|sql|bak|ini|yml|yaml|tar|gz|rar)$)#i', $p);
    }
}

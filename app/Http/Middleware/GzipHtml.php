<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GzipHtml
{
    public function handle(Request $r, Closure $next)
    {
        $res = $next($r);
        if (
            $r->isMethod('GET') && $res->getStatusCode() === 200 && function_exists('gzencode')
            && str_contains((string) $r->header('Accept-Encoding'), 'gzip') && !$res->headers->has('Content-Encoding')
            && str_starts_with((string) $res->headers->get('Content-Type'), 'text/html')
        ) {
            $c = $res->getContent();
            if (is_string($c) && strlen($c) > 1024) {
                $res->setContent(gzencode($c, 6));
                $res->headers->set('Content-Encoding', 'gzip');
                $res->headers->set('Vary', 'Accept-Encoding');
                $res->headers->remove('Content-Length');
            }
        }
        return $res;
    }
}

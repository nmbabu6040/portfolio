<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;

class Maintenance
{
    public function handle(Request $r, Closure $next)
    {
        $s = Setting::map();
        if (!empty($s['maintenance']) && !$r->user()) {
            return response()->view('errors.maintenance', ['s' => $s], 503)->header('Retry-After', '3600');
        }
        return $next($r);
    }
}

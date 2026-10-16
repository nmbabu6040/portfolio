<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Perm
{
    public function handle(Request $r, Closure $next, string $perm)
    {
        $u = $r->user();
        abort_unless($u && $u->hasPerm($perm), 403);
        return $next($r);
    }
}

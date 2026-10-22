<?php

namespace App\Http\Middleware;

use App\Support\Captcha;
use Closure;
use Illuminate\Http\Request;

class CaptchaCheck
{
    public function handle(Request $r, Closure $next)
    {
        if (Captcha::ok($r)) return $next($r);
        $to = preg_replace('/#.*$/', '', url()->previous());
        if (parse_url($to, PHP_URL_HOST) !== $r->getHost()) $to = url('/');
        if ($r->is('subscribe')) $to .= '#nl';
        if ($r->is('blog/*/comment')) $to .= '#comments';
        return redirect($to)
            ->withInput($r->except('_token', 'cf-turnstile-response'))
            ->with('captcha_err', 'Please complete the spam check and try again. If it does not appear, reload the page.');
    }
}

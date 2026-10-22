<?php
return [
    // enforce = block (default), report = only report in the browser console, off = no CSP
    'csp' => env('CSP_MODE', 'enforce'),
    // set to * only when the site is behind Cloudflare or another proxy, so HTTPS and visitor IPs are read correctly
    'proxies' => env('SECURITY_PROXIES'),
];

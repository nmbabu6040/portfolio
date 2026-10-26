<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        // $middleware->append(\App\Http\Middleware\GzipHtml::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Old addresses are redirected, other broken links are recorded in Admin > Redirects & 404.
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            return \App\Support\Redirects::handle404($request);
        });
    })->create();

<?php

namespace App\Providers;

use App\Models\{Entry, Post, User};
use App\Support\{Activity, LoginAlert};
use Illuminate\Auth\Events\{Login, Logout};
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\{Event, View};
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        // behind Cloudflare or another proxy: read the real HTTPS and visitor IP (set SECURITY_PROXIES=* in .env)
        if ($p = config('security.proxies')) {
            Request::setTrustedProxies($p === '*' ? ['*'] : explode(',', $p), Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);
        }
        $safe = function (callable $q) {
            try {
                return $q();
            } catch (\Throwable $e) {
                return collect();
            }
        };
        $ord = fn($q) => $q->orderBy('sort')->orderBy('id');

        View::composer('layouts.app', function ($view) use ($safe) {
            $view->with('fs', $safe(fn() => Entry::of('services')->newQuery()->orderBy('sort')->limit(6)->get(['title', 'slug'])));
            $view->with('fp', $safe(fn() => Post::where('published', 1)->latest()->limit(2)->get(['title', 'slug'])));
        });
        View::composer(['home', 'pages.about'], function ($view) use ($safe, $ord) {
            $view->with('reviews', $safe(fn() => $ord(Entry::of('testimonials')->newQuery()->where('visible', 1))->get()));
        });
        View::composer('pages.service', function ($view) use ($safe, $ord) {
            $slug = $view->getData()['sv']->slug ?? '';
            $view->with('faqs', $safe(fn() => $ord(Entry::of('faqs')->newQuery()->where('service', $slug))->get()));
        });
        View::composer('pages.services', function ($view) use ($safe, $ord) {
            $view->with('faqs', $safe(fn() => $ord(Entry::of('faqs')->newQuery()->where(fn($q) => $q->whereNull('service')->orWhere('service', '')))->get()));
        });

        // activity log + login alerts
        Event::listen(Login::class, fn($e) => LoginAlert::handle($e->user));
        Event::listen(Logout::class, fn($e) => $e->user ? Activity::note('logout', 'Logged out', null, null, $e->user) : null);
        User::created(fn($u) => Activity::note('created', 'Created user: ' . $u->email . ' (' . $u->role . ')', 'Users', $u->id));
        User::deleted(fn($u) => Activity::note('deleted', 'Deleted user: ' . $u->email, 'Users', $u->id));
        User::updated(function ($u) {
            if (!auth()->check()) return;
            $p = [];
            if ($u->wasChanged('role')) $p[] = 'role ' . $u->getOriginal('role') . ' to ' . $u->role;
            if ($u->wasChanged('active')) $p[] = $u->active ? 'activated' : 'deactivated';
            if ($u->wasChanged('password')) $p[] = 'password changed';
            if ($p) Activity::note('updated', 'User ' . $u->email . ': ' . implode(', ', $p), 'Users', $u->id);
        });
    }
}

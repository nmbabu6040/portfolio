<?php

namespace App\Providers;

use App\Models\{Entry, Post};
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}
    public function boot(): void
    {
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
    }
}

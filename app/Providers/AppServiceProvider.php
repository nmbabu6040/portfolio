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
        View::composer('layouts.app', function ($view) {
            try {
                $view->with('fs', Entry::of('services')->newQuery()->orderBy('sort')->limit(6)->get(['title', 'slug']));
                $view->with('fp', Post::where('published', 1)->latest()->limit(2)->get(['title', 'slug']));
            } catch (\Throwable $e) {
                $view->with('fs', collect())->with('fp', collect());
            }
        });
    }
}

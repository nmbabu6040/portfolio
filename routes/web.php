<?php

use App\Http\Controllers\{SiteController, AdminController};
use Illuminate\Support\Facades\Route;

Route::pattern('t', implode('|', array_merge(array_keys(config('admin')), ['messages'])));
Route::get('/', [SiteController::class, 'home']);
Route::get('/about', [SiteController::class, 'about']);
Route::get('/experience', [SiteController::class, 'experience']);
Route::get('/technology', [SiteController::class, 'technology']);
Route::get('/projects', [SiteController::class, 'projects']);
Route::get('/projects/{id}', [SiteController::class, 'project'])->whereNumber('id');
Route::get('/services', [SiteController::class, 'services']);
Route::get('/services/{slug}', [SiteController::class, 'service']);
Route::get('/contact', [SiteController::class, 'contactPage']);
Route::post('/contact', [SiteController::class, 'contact'])->middleware('throttle:5,1');
Route::get('/blog', [SiteController::class, 'blog']);
Route::get('/blog/{slug}', [SiteController::class, 'post']);
Route::get('/sitemap.xml', [SiteController::class, 'sitemap']);
Route::get('/robots.txt', fn() => response("User-agent: *\nDisallow: /admin\nSitemap: " . url('/sitemap.xml') . "\n")->header('Content-Type', 'text/plain'));
Route::get('/admin/login', [AdminController::class, 'loginForm'])->name('login');
Route::post('/admin/login', [AdminController::class, 'login'])->middleware('throttle:5,1');
Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard']);
    Route::get('settings', [AdminController::class, 'settings']);
    Route::post('settings', [AdminController::class, 'settingsSave']);
    Route::post('settings/test-mail', [AdminController::class, 'testMail']);
    Route::get('password', [AdminController::class, 'passwordForm']);
    Route::post('password', [AdminController::class, 'passwordSave']);
    Route::post('upload', [AdminController::class, 'upload']);
    Route::get('messages', [AdminController::class, 'messages']);
    Route::post('messages/{id}/read', [AdminController::class, 'read']);
    Route::post('logout', [AdminController::class, 'logout']);
    Route::get('{t}', [AdminController::class, 'index']);
    Route::get('{t}/create', [AdminController::class, 'form']);
    Route::get('{t}/{id}/edit', [AdminController::class, 'form']);
    Route::post('{t}', [AdminController::class, 'save']);
    Route::post('{t}/{id}', [AdminController::class, 'save']);
    Route::delete('{t}/{id}', [AdminController::class, 'destroy']);
});

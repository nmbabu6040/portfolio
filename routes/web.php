<?php

use App\Http\Controllers\{SiteController, AdminController};
use App\Http\Middleware\{Maintenance, TrackVisit};
use Illuminate\Support\Facades\Route;

Route::pattern('t', implode('|', array_merge(array_keys(config('admin')), ['messages'])));

Route::middleware([Maintenance::class, TrackVisit::class])->group(function () {
    Route::get('/', [SiteController::class, 'home']);
    Route::get('/about', [SiteController::class, 'about']);
    Route::get('/experience', [SiteController::class, 'experience']);
    Route::get('/technology', [SiteController::class, 'technology']);
    Route::get('/projects', [SiteController::class, 'projects']);
    Route::get('/projects/{id}', [SiteController::class, 'project'])->whereNumber('id');
    Route::get('/services', [SiteController::class, 'services']);
    Route::get('/services/{slug}', [SiteController::class, 'service']);
    Route::get('/contact', [SiteController::class, 'contactPage']);
    Route::get('/quote', [SiteController::class, 'quotePage']);
    Route::get('/privacy', [SiteController::class, 'privacy']);
    Route::get('/affiliate-disclosure', [SiteController::class, 'affiliate']);
    Route::get('/blog', [SiteController::class, 'blog']);
    Route::get('/blog/category/{slug}', [SiteController::class, 'category']);
    Route::get('/blog/{slug}', [SiteController::class, 'post']);
});

Route::post('/contact', [SiteController::class, 'contact'])->middleware([Maintenance::class, 'throttle:5,1']);
Route::post('/quote', [SiteController::class, 'quote'])->middleware([Maintenance::class, 'throttle:5,1']);
Route::post('/subscribe', [SiteController::class, 'subscribe'])->middleware([Maintenance::class, 'throttle:5,1']);
Route::get('/unsubscribe/{token}', [SiteController::class, 'unsubscribePage']);
Route::post('/unsubscribe/{token}', [SiteController::class, 'unsubscribe'])->middleware('throttle:10,1');
Route::get('/sitemap.xml', [SiteController::class, 'sitemap']);
Route::get('/robots.txt', fn() => response("User-agent: *\nDisallow: /admin\nDisallow: /unsubscribe\nSitemap: " . url('/sitemap.xml') . "\n")->header('Content-Type', 'text/plain'));

Route::get('/admin/login', [AdminController::class, 'loginForm'])->name('login');
Route::post('/admin/login', [AdminController::class, 'login'])->middleware('throttle:5,1');
Route::get('/admin/two-factor', [AdminController::class, 'twoFactorForm']);
Route::post('/admin/two-factor', [AdminController::class, 'twoFactorCheck'])->middleware('throttle:10,1');
Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard']);
    Route::get('error-preview/{code}', fn($code) => response()->view("errors.$code", [], (int) $code))->whereIn('code', ['401', '403', '404', '405', '419', '429', '500', '503']);
    Route::get('settings', [AdminController::class, 'settings']);
    Route::post('settings', [AdminController::class, 'settingsSave']);
    Route::post('settings/test-mail', [AdminController::class, 'testMail']);
    Route::get('password', [AdminController::class, 'passwordForm']);
    Route::post('password', [AdminController::class, 'passwordSave']);
    Route::get('security', [AdminController::class, 'security']);
    Route::post('security/enable', [AdminController::class, 'securityEnable']);
    Route::post('security/recovery', [AdminController::class, 'securityRecovery']);
    Route::post('security/disable', [AdminController::class, 'securityDisable']);
    Route::get('backup', [AdminController::class, 'backupForm']);
    Route::post('backup/download', [AdminController::class, 'backupDownload']);
    Route::post('upload', [AdminController::class, 'upload']);
    Route::get('media', [AdminController::class, 'media']);
    Route::post('media', [AdminController::class, 'mediaUpload']);
    Route::post('media/cleanup', [AdminController::class, 'mediaCleanup']);
    Route::delete('media/{name}', [AdminController::class, 'mediaDelete'])->where('name', '[A-Za-z0-9._-]+');
    Route::get('messages', [AdminController::class, 'messages']);
    Route::post('messages/{id}/read', [AdminController::class, 'read']);
    Route::get('subscribers', [AdminController::class, 'subscribers']);
    Route::get('subscribers/export', [AdminController::class, 'subscribersExport']);
    Route::delete('subscribers/{id}', [AdminController::class, 'subscriberDelete']);
    Route::post('logout', [AdminController::class, 'logout']);
    Route::get('{t}', [AdminController::class, 'index']);
    Route::get('{t}/create', [AdminController::class, 'form']);
    Route::get('{t}/{id}/edit', [AdminController::class, 'form']);
    Route::post('{t}/reorder', [AdminController::class, 'reorder']);
    Route::post('{t}', [AdminController::class, 'save']);
    Route::post('{t}/{id}', [AdminController::class, 'save']);
    Route::delete('{t}/{id}', [AdminController::class, 'destroy']);
});

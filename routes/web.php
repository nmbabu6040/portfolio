<?php

use App\Http\Controllers\{SiteController, AdminController, DocumentController, GalleryController};
use App\Http\Middleware\{Maintenance, Perm, TrackVisit};
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
    Route::get('/book', [SiteController::class, 'book']);
    Route::get('/privacy', [SiteController::class, 'privacy']);
    Route::get('/affiliate-disclosure', [SiteController::class, 'affiliate']);
    Route::get('/blog', [SiteController::class, 'blog']);
    Route::get('/blog/category/{slug}', [SiteController::class, 'category']);
    Route::get('/blog/{slug}', [SiteController::class, 'post']);
});

Route::post('/contact', [SiteController::class, 'contact'])->middleware([Maintenance::class, 'throttle:5,1']);
Route::post('/quote', [SiteController::class, 'quote'])->middleware([Maintenance::class, 'throttle:5,1']);
Route::post('/subscribe', [SiteController::class, 'subscribe'])->middleware([Maintenance::class, 'throttle:5,1']);

Route::post('/blog/{slug}/comment', [SiteController::class, 'comment'])->middleware([Maintenance::class, 'throttle:6,1']);
Route::post('/blog/{slug}/like', [SiteController::class, 'like'])->middleware([Maintenance::class, 'throttle:30,1']);
Route::post('/blog/{slug}/share', [SiteController::class, 'share'])->middleware([Maintenance::class, 'throttle:30,1']);
Route::post('/cta', [SiteController::class, 'cta'])->middleware('throttle:60,1');
Route::get('/unsubscribe/{token}', [SiteController::class, 'unsubscribePage']);
Route::post('/unsubscribe/{token}', [SiteController::class, 'unsubscribe'])->middleware('throttle:10,1');
Route::get('/sitemap.xml', [SiteController::class, 'sitemap']);
Route::get('/robots.txt', fn() => response("User-agent: *\nDisallow: /admin\nDisallow: /unsubscribe\nSitemap: " . url('/sitemap.xml') . "\n")->header('Content-Type', 'text/plain'));

Route::get('/admin/login', [AdminController::class, 'loginForm'])->name('login');
Route::post('/admin/login', [AdminController::class, 'login'])->middleware('throttle:5,1');
Route::get('/admin/two-factor', [AdminController::class, 'twoFactorForm']);
Route::post('/admin/two-factor', [AdminController::class, 'twoFactorCheck'])->middleware('throttle:10,1');

Route::middleware(['auth', Perm::class . ':dashboard'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard']);
    Route::get('error-preview/{code}', fn($code) => response()->view("errors.$code", [], (int) $code))->whereIn('code', ['401', '403', '404', '405', '419', '429', '500', '503']);
    Route::get('password', [AdminController::class, 'passwordForm']);
    Route::post('password', [AdminController::class, 'passwordSave']);
    Route::get('security', [AdminController::class, 'security']);
    Route::post('security/enable', [AdminController::class, 'securityEnable']);
    Route::post('security/recovery', [AdminController::class, 'securityRecovery']);
    Route::post('security/disable', [AdminController::class, 'securityDisable']);
    Route::post('security/alerts', [AdminController::class, 'securityAlerts']);
    Route::post('logout', [AdminController::class, 'logout']);

    Route::middleware(Perm::class . ':media')->group(function () {
        Route::post('upload', [AdminController::class, 'upload']);
        Route::get('media', [AdminController::class, 'media']);
        Route::post('media', [AdminController::class, 'mediaUpload']);
        Route::get('media/picker', [AdminController::class, 'mediaPicker']);
    });
    Route::middleware(Perm::class . ':media.delete')->group(function () {
        Route::post('media/cleanup', [AdminController::class, 'mediaCleanup']);
        Route::delete('media/{name}', [AdminController::class, 'mediaDelete'])->where('name', '[A-Za-z0-9._-]+');
    });
    Route::middleware(Perm::class . ':messages')->group(function () {
        Route::get('messages', [AdminController::class, 'messages']);
        Route::post('messages/{id}/read', [AdminController::class, 'read']);
        Route::get('subscribers', [AdminController::class, 'subscribers']);
        Route::get('subscribers/export', [AdminController::class, 'subscribersExport']);
        Route::get('leads', [AdminController::class, 'leads']);
        Route::get('leads/{id}', [AdminController::class, 'lead']);
        Route::post('leads/{id}', [AdminController::class, 'leadSave']);
        Route::post('leads/{id}/stage', [AdminController::class, 'leadStage']);
        Route::get('conversions', [AdminController::class, 'conversions']);
        Route::delete('subscribers/{id}', [AdminController::class, 'subscriberDelete']);
    });
    Route::middleware(Perm::class . ':posts.own')->group(function () {
        Route::get('comments', [AdminController::class, 'comments']);
        Route::post('comments/{id}/status', [AdminController::class, 'commentStatus']);
        Route::post('comments/{id}/reply', [AdminController::class, 'commentReply']);
        Route::delete('comments/{id}', [AdminController::class, 'commentDelete']);
    });
    Route::middleware(Perm::class . ':settings')->group(function () {
        Route::get('settings', [AdminController::class, 'settings']);
        Route::post('settings', [AdminController::class, 'settingsSave']);
        Route::post('settings/test-mail', [AdminController::class, 'testMail']);
    });
    Route::middleware(Perm::class . ':backup')->group(function () {
        Route::get('backup', [AdminController::class, 'backupForm']);
        Route::post('backup/download', [AdminController::class, 'backupDownload']);
        Route::post('backup/settings', [AdminController::class, 'backupSettings']);
        Route::post('backup/run', [AdminController::class, 'backupRun']);
        Route::get('backup/file/{name}', [AdminController::class, 'backupFile'])->where('name', 'portfolio-[0-9-]+\.zip');
        Route::delete('backup/file/{name}', [AdminController::class, 'backupRemove'])->where('name', 'portfolio-[0-9-]+\.zip');
    });
    Route::get('activity', [AdminController::class, 'activity'])->middleware(Perm::class . ':activity');
    Route::middleware(Perm::class . ':users')->group(function () {
        Route::get('users', [AdminController::class, 'users']);
        Route::get('users/create', [AdminController::class, 'userForm']);
        Route::get('users/{id}/edit', [AdminController::class, 'userForm']);
        Route::post('users', [AdminController::class, 'userSave']);
        Route::post('users/{id}', [AdminController::class, 'userSave']);
        Route::delete('users/{id}', [AdminController::class, 'userDelete']);
    });

    Route::middleware(Perm::class . ':documents')->group(function () {
        Route::get('documents', [DocumentController::class, 'index']);
        Route::get('documents/create', [DocumentController::class, 'form']);
        Route::get('documents/{id}/edit', [DocumentController::class, 'form']);
        Route::get('documents/{id}/pdf', [DocumentController::class, 'pdf']);
        Route::post('documents', [DocumentController::class, 'save']);
        Route::post('documents/{id}', [DocumentController::class, 'save']);
        Route::post('documents/{id}/send', [DocumentController::class, 'send']);
        Route::post('documents/{id}/convert', [DocumentController::class, 'convert']);
        Route::post('documents/{id}/duplicate', [DocumentController::class, 'duplicate']);
        Route::post('documents/{id}/status', [DocumentController::class, 'status']);
        Route::delete('documents/{id}', [DocumentController::class, 'destroy']);
    });

    Route::post('projects/{id}/gallery', [GalleryController::class, 'add']);
    Route::post('projects/{id}/gallery/order', [GalleryController::class, 'order']);
    Route::post('gallery/{mid}/caption', [GalleryController::class, 'caption']);
    Route::delete('gallery/{mid}', [GalleryController::class, 'destroy']);

    Route::get('{t}', [AdminController::class, 'index']);
    Route::get('{t}/create', [AdminController::class, 'form']);
    Route::get('{t}/{id}/edit', [AdminController::class, 'form']);
    Route::post('{t}/reorder', [AdminController::class, 'reorder']);
    Route::post('{t}', [AdminController::class, 'save']);
    Route::post('{t}/{id}', [AdminController::class, 'save']);
    Route::delete('{t}/{id}', [AdminController::class, 'destroy']);
});

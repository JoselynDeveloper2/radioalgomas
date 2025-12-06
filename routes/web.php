<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\SimpleStreamProxyController;
use App\Http\Controllers\StreamProxyController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

// Ruta principal - redirigir al blog
Route::get('/', [BlogController::class, 'index'])->name('home');

// Rutas del blog
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/categoria/{slug}', [BlogController::class, 'category'])->name('category');
    Route::get('/etiqueta/{slug}', [BlogController::class, 'tag'])->name('tag');
    Route::get('/{slug}', [BlogController::class, 'show'])->name('show');
});

// Feeds y sitemap
Route::get('/rss', [BlogController::class, 'rss'])->name('blog.rss');
Route::get('/sitemap.xml', [BlogController::class, 'sitemap'])->name('sitemap');

// Stream Proxy Routes
Route::prefix('stream-proxy')->name('stream.proxy.')->group(function () {
    Route::get('/active', [StreamProxyController::class, 'activeStream'])->name('active');
    Route::get('/id/{id}', [StreamProxyController::class, 'streamById'])->name('id');
    Route::get('/external/{url}', [StreamProxyController::class, 'proxyExternal'])->name('external');
    Route::options('/active', [StreamProxyController::class, 'options']);
    Route::options('/id/{id}', [StreamProxyController::class, 'options']);
    Route::options('/external/{url}', [StreamProxyController::class, 'options']);
    
    // Debug route
    Route::get('/debug', function() {
        $activeUrl = \App\Models\PlayerUrl::getActive();
        return response()->json([
            'active_url' => $activeUrl ? $activeUrl->toArray() : null,
            'proxy_url' => $activeUrl ? route('stream.proxy.active') : null,
            'original_test' => $activeUrl ? $activeUrl->url : null,
        ]);
    })->name('debug');
});

// Simple Stream Proxy (alternative)
Route::get('/stream', [SimpleStreamProxyController::class, 'proxy'])->name('stream.simple');
Route::options('/stream', [SimpleStreamProxyController::class, 'options']);

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

Route::get('/ads/{ad}/click', [App\Http\Controllers\AdController::class, 'click'])->name('ads.click');

require __DIR__.'/auth.php';

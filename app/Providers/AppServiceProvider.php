<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Article;
use App\Models\Category;
use App\Models\PlayerUrl;
use App\Observers\ArticleObserver;
use App\Observers\PlayerUrlObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configurar Carbon para español
        Carbon::setLocale('es');
        
        // Registrar observers
        Article::observe(ArticleObserver::class);
        PlayerUrl::observe(PlayerUrlObserver::class);
        
        // View Composers para compartir datos con las vistas
        View::composer(['layouts.blog', 'components.blog.header', 'components.blog.footer'], function ($view) {
            try {
                $categories = Category::active()
                    ->ordered()
                    ->whereHas('articles', function($query) {
                        $query->where('status', Article::STATUS_PUBLISHED)
                              ->where('published_at', '<=', now());
                    })
                    ->get();
            } catch (\Exception $e) {
                $categories = collect(); // Fallback en caso de error
            }
            $view->with('categories', $categories);
        });
    }
}

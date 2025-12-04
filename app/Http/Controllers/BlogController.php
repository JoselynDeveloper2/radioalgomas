<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Illuminate\Http\Response;

class BlogController extends Controller
{
    /**
     * Mostrar la página principal del blog
     */
    public function index(Request $request): View
    {
        $query = Article::with(['category', 'user', 'tags'])
            ->published()
            ->latest('published_at');

        // Filtrar por categoría si se especifica
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filtrar por etiqueta si se especifica
        if ($request->has('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('slug', $request->tag);
            });
        }

        // Búsqueda por texto
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('excerpt', 'like', "%{$searchTerm}%")
                  ->orWhere('content', 'like', "%{$searchTerm}%");
            });
        }

        // Obtener artículos destacados con prioridad para categoría reciente
        $featuredArticles = $this->getFeaturedArticlesWithRotation();

        // Excluir artículos destacados de la lista principal
        if ($featuredArticles->isNotEmpty()) {
            $featuredIds = $featuredArticles->pluck('id');
            $query->whereNotIn('id', $featuredIds);
        }

        $articles = $query->paginate(9);

        $categories = Category::active()->ordered()->get();
        $popularTags = Tag::withCount(['publishedArticles'])
            ->having('published_articles_count', '>', 0)
            ->orderBy('published_articles_count', 'desc')
            ->take(10)
            ->get();

        return view('blog.index', compact(
            'articles',
            'featuredArticles',
            'categories',
            'popularTags'
        ));
    }

    /**
     * Mostrar un artículo específico
     */
    public function show(string $slug): View
    {
        $article = Article::with(['category', 'user', 'tags'])
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        // Incrementar contador de vistas
        $article->increment('views_count');

        // Artículos relacionados
        $relatedArticles = Article::with(['category', 'user'])
            ->published()
            ->where('id', '!=', $article->id)
            ->where('category_id', $article->category_id)
            ->latest('published_at')
            ->take(4)
            ->get();

        // Si no hay suficientes artículos relacionados por categoría,
        // completar con artículos de otras categorías
        if ($relatedArticles->count() < 4) {
            $additionalArticles = Article::with(['category', 'user'])
                ->published()
                ->where('id', '!=', $article->id)
                ->whereNotIn('id', $relatedArticles->pluck('id'))
                ->latest('published_at')
                ->take(4 - $relatedArticles->count())
                ->get();

            $relatedArticles = $relatedArticles->merge($additionalArticles);
        }

        return view('blog.show', compact('article', 'relatedArticles'));
    }

    /**
     * Mostrar artículos de una categoría específica
     */
    public function category(string $slug): View
    {
        $category = Category::where('slug', $slug)
            ->active()
            ->firstOrFail();

        $articles = Article::with(['category', 'user', 'tags'])
            ->published()
            ->where('category_id', $category->id)
            ->latest('published_at')
            ->paginate(12);

        $categories = Category::active()->ordered()->get();

        return view('blog.category', compact('category', 'articles', 'categories'));
    }

    /**
     * Mostrar artículos con una etiqueta específica
     */
    public function tag(string $slug): View
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $articles = Article::with(['category', 'user', 'tags'])
            ->published()
            ->whereHas('tags', function ($query) use ($tag) {
                $query->where('tags.id', $tag->id);
            })
            ->latest('published_at')
            ->paginate(12);

        $categories = Category::active()->ordered()->get();
        $popularTags = Tag::withCount(['publishedArticles'])
            ->having('published_articles_count', '>', 0)
            ->orderBy('published_articles_count', 'desc')
            ->take(10)
            ->get();

        return view('blog.tag', compact('tag', 'articles', 'categories', 'popularTags'));
    }

    /**
     * Generar RSS feed
     */
    public function rss(): Response
    {
        $articles = Article::with(['category', 'user'])
            ->published()
            ->latest('published_at')
            ->take(20)
            ->get();

        $content = view('blog.rss', compact('articles'))->render();

        return response($content, 200)
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    /**
     * Generar sitemap XML
     */
    public function sitemap(): Response
    {
        $articles = Article::with('tags')
            ->published()
            ->select('slug', 'updated_at', 'published_at', 'title', 'excerpt', 'featured_image')
            ->latest('published_at')
            ->limit(100) // Limitamos a 100 para asegurar que Google vea las más recientes y no se sature
            ->get();

        $categories = Category::active()
            ->select('slug', 'updated_at')
            ->get();

        $tags = Tag::select('slug', 'updated_at')->get();

        $content = view('blog.sitemap', compact('articles', 'categories', 'tags'))->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Obtener artículos destacados con prioridad para la categoría recién importada
     */
    private function getFeaturedArticlesWithRotation()
    {
        // Buscar la categoría más recientemente importada (en los últimos 15 minutos)
        $recentCategory = $this->getRecentlyImportedCategory();
        
        $featuredArticles = collect();
        
        if ($recentCategory) {
            // Priorizar artículos de la categoría recién importada (máximo 2)
            $categoryArticles = Article::with(['category', 'user'])
                ->published()
                ->where('category_id', $recentCategory->id)
                ->latest('published_at')
                ->take(2)
                ->get();
            
            $featuredArticles = $featuredArticles->concat($categoryArticles);
        }
        
        // Completar con otros artículos destacados si no tenemos 3
        if ($featuredArticles->count() < 3) {
            $excludeIds = $featuredArticles->pluck('id')->toArray();
            $remainingSlots = 3 - $featuredArticles->count();
            
            $otherArticles = Article::with(['category', 'user'])
                ->published()
                ->when(!empty($excludeIds), function($q) use ($excludeIds) {
                    return $q->whereNotIn('id', $excludeIds);
                })
                ->latest('published_at')
                ->take($remainingSlots)
                ->get();
            
            $featuredArticles = $featuredArticles->concat($otherArticles);
        }
        
        return $featuredArticles->take(3);
    }

    /**
     * Obtener la categoría importada más recientemente
     */
    private function getRecentlyImportedCategory(): ?Category
    {
        $categories = Category::active()
            ->whereHas('rssFeeds', function($q) {
                $q->active();
            })
            ->get();
        
        $recentCategory = null;
        $mostRecentTime = null;
        
        foreach ($categories as $category) {
            $lastImport = Cache::get("category_rotation_{$category->id}");
            
            if ($lastImport && (!$mostRecentTime || $lastImport > $mostRecentTime)) {
                $mostRecentTime = $lastImport;
                $recentCategory = $category;
            }
        }
        
        // Solo considerar si fue importada en los últimos 15 minutos
        if ($recentCategory && $mostRecentTime && now()->diffInMinutes($mostRecentTime) <= 15) {
            return $recentCategory;
        }
        
        return null;
    }
}

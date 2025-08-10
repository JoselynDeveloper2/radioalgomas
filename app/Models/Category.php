<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'is_active',
        'sort_order',
        // Campos SEO
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    protected $dates = [
        'deleted_at'
    ];

    // Relaciones
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function publishedArticles(): HasMany
    {
        return $this->hasMany(Article::class)
                    ->where('status', Article::STATUS_PUBLISHED)
                    ->where('published_at', '<=', now())
                    ->orderBy('published_at', 'desc');
    }

    public function rssFeeds(): HasMany
    {
        return $this->hasMany(RssFeed::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Accessors
    public function getMetaTitleAttribute($value): string
    {
        return $value ?: $this->name . ' - ' . config('app.name');
    }

    public function getMetaDescriptionAttribute($value): string
    {
        return $value ?: ($this->description ?: 'Noticias y artículos sobre ' . $this->name);
    }

    public function getOgTitleAttribute($value): string
    {
        return $value ?: $this->meta_title;
    }

    public function getOgDescriptionAttribute($value): string
    {
        return $value ?: $this->meta_description;
    }

    public function getArticlesCountAttribute(): int
    {
        return $this->articles()->count();
    }

    public function getPublishedArticlesCountAttribute(): int
    {
        return $this->publishedArticles()->count();
    }

    public function getUrlAttribute(): string
    {
        return url('/categoria/' . $this->slug);
    }

    // Métodos de utilidad
    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function hasArticles(): bool
    {
        return $this->articles()->exists();
    }

    public function hasPublishedArticles(): bool
    {
        return $this->publishedArticles()->exists();
    }

    // Boot method para eventos del modelo
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
            
            if (is_null($category->sort_order)) {
                $category->sort_order = static::max('sort_order') + 1;
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->getOriginal('slug'))) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
}
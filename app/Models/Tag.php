<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'is_active',
        // Campos SEO
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    protected $dates = [
        'deleted_at'
    ];

    // Relaciones
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_tag');
    }

    public function publishedArticles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_tag')
                    ->where('status', Article::STATUS_PUBLISHED)
                    ->where('published_at', '<=', now())
                    ->orderBy('published_at', 'desc');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePopular($query, int $limit = 10)
    {
        return $query->withCount(['publishedArticles'])
                    ->orderBy('published_articles_count', 'desc')
                    ->limit($limit);
    }

    public function scopeAlphabetical($query)
    {
        return $query->orderBy('name');
    }

    // Accessors
    public function getMetaTitleAttribute($value): string
    {
        return $value ?: $this->name . ' - ' . config('app.name');
    }

    public function getMetaDescriptionAttribute($value): string
    {
        return $value ?: ($this->description ?: 'Artículos etiquetados con ' . $this->name);
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
        return url('/etiqueta/' . $this->slug);
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

    public function getUsageCount(): int
    {
        return $this->publishedArticles()->count();
    }

    // Métodos estáticos
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    public static function getPopularTags(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()
                    ->has('publishedArticles')
                    ->withCount(['publishedArticles'])
                    ->orderBy('published_articles_count', 'desc')
                    ->limit($limit)
                    ->get();
    }

    public static function createFromString(string $tagString): array
    {
        $tagNames = array_map('trim', explode(',', $tagString));
        $tags = [];

        foreach ($tagNames as $tagName) {
            if (empty($tagName)) {
                continue;
            }

            $tag = static::firstOrCreate(
                ['name' => $tagName],
                [
                    'slug' => Str::slug($tagName),
                    'is_active' => true
                ]
            );

            $tags[] = $tag;
        }

        return $tags;
    }

    // Boot method para eventos del modelo
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });

        static::updating(function ($tag) {
            if ($tag->isDirty('name') && empty($tag->getOriginal('slug'))) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }
}
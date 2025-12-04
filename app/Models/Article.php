<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status',
        'published_at',
        'category_id',
        'user_id',
        'is_featured',
        // Campos RSS
        'rss_feed_id',
        'external_id',
        'source_url',
        'is_imported',
        'import_metadata',
        // Campos SEO
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image',
        'schema_markup',
        'canonical_url',
        'reading_time'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'reading_time' => 'integer',
        'category_id' => 'integer',
        'user_id' => 'integer',
        'rss_feed_id' => 'integer',
        'schema_markup' => 'array',
        'is_imported' => 'boolean',
        'is_featured' => 'boolean',
        'import_metadata' => 'array'
    ];

    protected $dates = [
        'published_at',
        'deleted_at'
    ];

    // Estados del artículo
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_ARCHIVED = 'archived';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Borrador',
            self::STATUS_PUBLISHED => 'Publicado',
            self::STATUS_SCHEDULED => 'Programado',
            self::STATUS_ARCHIVED => 'Archivado'
        ];
    }

    // Relaciones
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'article_tag');
    }

    public function rssFeed(): BelongsTo
    {
        return $this->belongsTo(RssFeed::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
                    ->where('published_at', '<=', now());
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED)
                    ->where('published_at', '>', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeImported($query)
    {
        return $query->where('is_imported', true);
    }

    public function scopeManual($query)
    {
        return $query->where('is_imported', false);
    }

    public function scopeFromFeed($query, int $feedId)
    {
        return $query->where('rss_feed_id', $feedId);
    }

    // Accessors
    public function getExcerptAttribute($value): string
    {
        if ($value) {
            return $value;
        }
        
        return Str::limit(strip_tags($this->content), 160);
    }

    public function getReadingTimeAttribute($value): int
    {
        if ($value) {
            return (int) $value;
        }
        
        $wordCount = str_word_count(strip_tags($this->content));
        return (int) max(1, ceil($wordCount / 200)); // 200 palabras por minuto
    }

    public function getMetaTitleAttribute($value): string
    {
        return $value ?: $this->title;
    }

    public function getMetaDescriptionAttribute($value): string
    {
        return $value ?: $this->excerpt;
    }

    public function getOgTitleAttribute($value): string
    {
        return $value ?: $this->meta_title;
    }

    public function getOgDescriptionAttribute($value): string
    {
        return $value ?: $this->meta_description;
    }

    public function getCanonicalUrlAttribute($value): string
    {
        return $value ?: route('blog.show', $this->slug);
    }

    // Métodos de utilidad
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && 
               $this->published_at <= now();
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED && 
               $this->published_at > now();
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? 'Desconocido';
    }

    public function getPublishedAtFormattedAttribute(): string
    {
        if (!$this->published_at) {
            return 'No publicado';
        }
        
        return $this->published_at->format('d/m/Y H:i');
    }

    public function getPublishedAtHumanAttribute(): string
    {
        if (!$this->published_at) {
            return 'No publicado';
        }
        
        return $this->published_at->diffForHumans();
    }

    // Generar Schema.org JSON-LD
    public function generateSchemaMarkup(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $this->title,
            'description' => $this->meta_description,
            'image' => $this->featured_image ? url($this->featured_image) : null,
            'author' => [
                '@type' => 'Person',
                'name' => $this->user->name ?? 'Anónimo'
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => url('/images/logo.png')
                ]
            ],
            'datePublished' => $this->published_at?->toISOString(),
            'dateModified' => $this->updated_at->toISOString(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $this->canonical_url
            ],
            'articleSection' => $this->category->name ?? null,
            'keywords' => $this->meta_keywords,
            'wordCount' => str_word_count(strip_tags($this->content)),
            'timeRequired' => 'PT' . $this->reading_time . 'M'
        ];
    }

    // Boot method para eventos del modelo
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
            
            // Generar tiempo de lectura automáticamente
            if (empty($article->reading_time)) {
                $wordCount = str_word_count(strip_tags($article->content));
                $article->reading_time = (int) max(1, ceil($wordCount / 200));
            }
        });

        static::updating(function ($article) {
            // Actualizar slug si el título cambió
            if ($article->isDirty('title') && empty($article->getOriginal('slug'))) {
                $article->slug = Str::slug($article->title);
            }
            
            // Actualizar tiempo de lectura si el contenido cambió
            if ($article->isDirty('content')) {
                $wordCount = str_word_count(strip_tags($article->content));
                $article->reading_time = (int) max(1, ceil($wordCount / 200));
            }
        });

        static::saved(function ($article) {
            // Generar schema markup automáticamente
            if (empty($article->schema_markup)) {
                $article->withoutEvents(function () use ($article) {
                    $article->update([
                        'schema_markup' => $article->generateSchemaMarkup()
                    ]);
                });
            }
            
            // Lógica para auto-destacar los últimos 3 artículos
            if (($article->wasRecentlyCreated || $article->isDirty('status')) && $article->status === self::STATUS_PUBLISHED) {
                // Marcar el artículo actual como destacado si no lo está ya
                if (!$article->is_featured) {
                    $article->withoutEvents(function () use ($article) {
                        $article->update(['is_featured' => true]);
                    });
                }

                // Obtener los IDs de los 3 artículos destacados más recientes
                $latestFeaturedIds = static::published()
                    ->featured()
                    ->latest('published_at')
                    ->take(3)
                    ->pluck('id');

                // Quitar el estado de "destacado" de los artículos más antiguos
                if ($latestFeaturedIds->count() > 0) {
                    static::withoutEvents(function () use ($latestFeaturedIds) {
                        static::where('is_featured', true)
                            ->whereNotIn('id', $latestFeaturedIds)
                            ->update(['is_featured' => false]);
                    });
                }
            }
        });
    }
}
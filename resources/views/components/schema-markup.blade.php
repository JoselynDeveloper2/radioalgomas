@props(['article'])

@php
    // Se genera en cada visita: la copia guardada en `schema_markup` conserva el dominio y el
    // nombre del sitio del momento en que se creó la nota.
    $schemaData = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'headline' => $article->title,
        'description' => $article->excerpt ?: Str::limit(strip_tags($article->content), 160),
        'image' => $article->featured_image ? url(Storage::url($article->featured_image)) : null,
        'author' => [
            '@type' => 'Person',
            'name' => $article->user->name ?? 'Redacción',
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => config('app.name'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset('images/logo.png'),
            ],
        ],
        'datePublished' => $article->published_at?->toIso8601String(),
        'dateModified' => $article->updated_at?->toIso8601String(),
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => $article->publicCanonicalUrl(),
        ],
        'articleSection' => $article->category->name ?? null,
        'keywords' => $article->tags->isNotEmpty() ? $article->tags->pluck('name')->implode(', ') : null,
        'timeRequired' => $article->reading_time ? 'PT' . $article->reading_time . 'M' : null,
        'wordCount' => $article->content ? str_word_count(strip_tags($article->content)) : null,
    ]);
@endphp

<script type="application/ld+json">
{!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
</script>

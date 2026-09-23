@props([
    'article'
])

@php
    // Get schema markup from article or generate it
    $schemaData = $article->schema_markup && is_array($article->schema_markup) 
        ? $article->schema_markup 
        : $article->generateSchemaMarkup();
        
    // Ensure we have valid schema data
    if (empty($schemaData) || !is_array($schemaData)) {
        $schemaData = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article->title,
            'description' => $article->excerpt ?? Str::limit(strip_tags($article->content), 160),
            'author' => [
                '@type' => 'Person',
                'name' => $article->user->name ?? 'Anónimo'
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name', 'RadioAlgoMas'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('images/radioalgomas-logo-3-insignia.svg')
                ]
            ],
            'datePublished' => $article->published_at?->toISOString(),
            'dateModified' => $article->updated_at?->toISOString(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => route('blog.show', $article->slug)
            ]
        ];
        
        // Add image if available
        if ($article->featured_image) {
            $schemaData['image'] = [
                '@type' => 'ImageObject',
                'url' => Storage::url($article->featured_image),
                'width' => 800,
                'height' => 600
            ];
        }
        
        // Add category if available
        if ($article->category) {
            $schemaData['articleSection'] = $article->category->name;
        }
        
        // Add keywords from tags if available
        if ($article->tags && $article->tags->count() > 0) {
            $schemaData['keywords'] = $article->tags->pluck('name')->implode(', ');
        }
        
        // Add reading time
        if ($article->reading_time) {
            $schemaData['timeRequired'] = 'PT' . $article->reading_time . 'M';
        }
        
        // Add word count
        if ($article->content) {
            $schemaData['wordCount'] = str_word_count(strip_tags($article->content));
        }
    }
@endphp

<script type="application/ld+json">
{!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
</script>
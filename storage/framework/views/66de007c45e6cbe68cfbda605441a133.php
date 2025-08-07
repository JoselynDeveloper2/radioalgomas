<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'article'
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'article'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
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
                'name' => config('app.name', 'TuCanalTV'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('images/logo-tucanaltv.jpeg')
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
?>

<script type="application/ld+json">
<?php echo json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?>

</script><?php /**PATH C:\Users\ghati\Herd\tucanaltv\resources\views/components/schema-markup.blade.php ENDPATH**/ ?>
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    <!-- Homepage -->
    <url>
        <loc>{{ url('/') }}</loc>
        <lastmod>{{ now()->toISOString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Categories -->
    @foreach($categories as $category)
    <url>
        <loc>{{ route('blog.category', $category->slug) }}</loc>
        <lastmod>{{ $category->updated_at ? $category->updated_at->toISOString() : now()->toISOString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach

    <!-- Tags -->
    @foreach($tags as $tag)
    <url>
        <loc>{{ route('blog.tag', $tag->slug) }}</loc>
        <lastmod>{{ $tag->updated_at ? $tag->updated_at->toISOString() : now()->toISOString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    @endforeach

    <!-- Articles -->
    @foreach($articles as $article)
    <url>
        <loc>{{ route('blog.show', $article->slug) }}</loc>
        <lastmod>{{ $article->updated_at ? $article->updated_at->toISOString() : now()->toISOString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
        
        <!-- Google News Sitemap -->
        <news:news>
            <news:publication>
                <news:name>{{ config('app.name') }}</news:name>
                <news:language>es</news:language>
            </news:publication>
            <news:publication_date>{{ $article->published_at ? $article->published_at->toISOString() : $article->updated_at->toISOString() }}</news:publication_date>
            <news:title><![CDATA[{{ $article->title }}]]></news:title>
            <news:keywords>{{ $article->tags->pluck('name')->implode(', ') }}</news:keywords>
        </news:news>
        
        <!-- Image Sitemap -->
        @if($article->featured_image)
        <image:image>
            <image:loc>{{ url($article->featured_image) }}</image:loc>
            <image:title><![CDATA[{{ $article->title }}]]></image:title>
            <image:caption><![CDATA[{{ $article->excerpt }}]]></image:caption>
        </image:image>
        @endif
    </url>
    @endforeach
</urlset>
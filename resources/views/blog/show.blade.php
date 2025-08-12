@extends('layouts.blog')

@section('title', $article->seo_title ?: $article->meta_title ?: $article->title)
@section('meta_description', $article->seo_meta_description ?: $article->meta_description ?: $article->excerpt)
@section('meta_keywords', $article->meta_keywords)
@section('canonical_url', $article->seo_canonical_url ?: $article->canonical_url ?: route('blog.show', $article->slug))

@section('og_type', 'article')
@section('og_title', $article->og_title ?: $article->seo_title ?: $article->title)
@section('og_description', $article->og_description ?: $article->seo_meta_description ?: $article->excerpt)
@section('og_image', $article->og_image ? Storage::url($article->og_image) : ($article->featured_image ? Storage::url($article->featured_image) : ''))

@section('twitter_title', $article->og_title ?: $article->seo_title ?: $article->title)
@section('twitter_description', $article->og_description ?: $article->seo_meta_description ?: $article->excerpt)
@section('twitter_image', $article->og_image ? Storage::url($article->og_image) : ($article->featured_image ? Storage::url($article->featured_image) : ''))

@push('schema')
<x-schema-markup :article="$article" />
@endpush

@section('content')
<div class="container mx-auto px-4 pt-8 pb-28">
    <div class="max-w-4xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <x-breadcrumb :items="[
                [
                    'label' => $article->category->name,
                    'url' => route('blog.category', $article->category->slug)
                ],
                [
                    'label' => Str::limit($article->title, 50),
                    'url' => null
                ]
            ]" />
        </nav>

        <!-- Article Header -->
        <header class="mb-8">
            <!-- Category Badge -->
            <div class="mb-4">
                <a href="{{ route('blog.category', $article->category->slug) }}" class="inline-block px-4 py-2 text-sm font-semibold rounded-full transition-colors" style="background-color: {{ $article->category->color }}20; color: {{ $article->category->color }}; border: 1px solid {{ $article->category->color }}40;">
                    {{ $article->category->name }}
                </a>
            </div>

            <!-- Title -->
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-6 leading-tight">
                {{ $article->title }}
            </h1>

            <!-- Excerpt -->
            @if($article->excerpt)
            <p class="text-xl text-gray-600 dark:text-gray-300 mb-6 leading-relaxed">
                {{ $article->excerpt }}
            </p>
            @endif

            <!-- Article Meta -->
            <div class="flex flex-wrap items-center gap-6 text-sm text-gray-600 dark:text-gray-400 mb-6">
                <!-- Author -->
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center mr-3">
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                            {{ substr($article->user->name, 0, 1) }}
                        </span>
                    </div>
                    <div>
                        <div class="font-medium text-gray-900 dark:text-white">{{ $article->user->name }}</div>
                        @if($article->user->bio)
                        <div class="text-xs">{{ Str::limit($article->user->bio, 50) }}</div>
                        @endif
                    </div>
                </div>

                <!-- Publication Date -->
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <time datetime="{{ $article->published_at->toISOString() }}">
                        {{ $article->published_at->format('d M Y') }}
                    </time>
                </div>

                <!-- Reading Time -->
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ $article->reading_time }} min lectura
                </div>

                <!-- Views -->
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    {{ number_format($article->views_count) }} vistas
                </div>
            </div>

            <!-- Social Share Buttons -->
            <div class="flex items-center space-x-4 mb-8">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Compartir:</span>
                <a href="https://twitter.com/intent/tweet?text={{ urlencode($article->title) }}&url={{ urlencode(route('blog.show', $article->slug)) }}" target="_blank" class="flex items-center px-3 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                    </svg>
                    Twitter
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('blog.show', $article->slug)) }}" target="_blank" class="flex items-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    Facebook
                </a>
                <a href="https://wa.me/?text={{ urlencode($article->title . ' ' . route('blog.show', $article->slug)) }}" target="_blank" class="flex items-center px-3 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                    </svg>
                    WhatsApp
                </a>
            </div>
        </header>

        <!-- Featured Image -->
        @if($article->featured_image)
        <div class="mb-8">
            <img src="{{ Storage::url($article->featured_image) }}" alt="{{ $article->title }}" class="w-auto h-auto rounded-lg shadow-lg">
        </div>
        @endif

        <!-- Article Content -->
        <article class="prose prose-lg dark:prose-invert max-w-none mb-12">
            {!! $article->content !!}
        </article>

        <!-- Tags -->
        @if($article->tags->count() > 0)
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Etiquetas</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($article->tags as $tag)
                <a href="{{ route('blog.tag', $tag->slug) }}" class="inline-block px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-sm hover:bg-blue-100 dark:hover:bg-blue-900 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                    {{ $tag->name }}
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Author Bio -->
        @if($article->user->bio)
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 mb-12">
            <div class="flex items-start">
                <div class="w-16 h-16 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                    <span class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                        {{ substr($article->user->name, 0, 1) }}
                    </span>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $article->user->name }}</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-3">{{ $article->user->bio }}</p>
                    <div class="flex space-x-4">
                        @if($article->user->website)
                        <a href="{{ $article->user->website }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">
                            Sitio web
                        </a>
                        @endif
                        @if($article->user->twitter)
                        <a href="https://twitter.com/{{ $article->user->twitter }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">
                            Twitter
                        </a>
                        @endif
                        @if($article->user->linkedin)
                        <a href="{{ $article->user->linkedin }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">
                            LinkedIn
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Related Articles -->
        @if($relatedArticles->count() > 0)
        <section class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Artículos Relacionados</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedArticles as $relatedArticle)
                <article class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    @if($relatedArticle->featured_image)
                    <div class="h-32 overflow-hidden">
                        <img src="{{ Storage::url($relatedArticle->featured_image) }}" alt="{{ $relatedArticle->title }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                    </div>
                    @endif
                    <div class="p-4">
                        <div class="flex items-center mb-2">
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full" style="background-color: {{ $relatedArticle->category->color }}20; color: {{ $relatedArticle->category->color }}">
                                {{ $relatedArticle->category->name }}
                            </span>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-2 hover:text-blue-600 dark:hover:text-blue-400 transition-colors line-clamp-2">
                            <a href="{{ route('blog.show', $relatedArticle->slug) }}">
                                {{ $relatedArticle->title }}
                            </a>
                        </h3>
                        <p class="text-xs text-gray-600 dark:text-gray-300 mb-2 line-clamp-2">
                            {{ $relatedArticle->excerpt }}
                        </p>
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span>{{ $relatedArticle->published_at->diffForHumans() }}</span>
                            <span>{{ $relatedArticle->reading_time }} min</span>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
        </section>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Copy URL to clipboard
    function copyUrl() {
        navigator.clipboard.writeText(window.location.href).then(function() {
            alert('URL copiada al portapapeles');
        });
    }
</script>
@endpush

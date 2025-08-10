@extends('layouts.blog')

@section('title', 'Etiqueta: ' . $tag->name . ' - ' . config('app.name'))
@section('meta_description', 'Artículos etiquetados con ' . $tag->name . ' en ' . config('app.name'))
@section('meta_keywords', $tag->name . ', noticias, actualidad')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Tag Header -->
    <div class="text-center mb-12">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full mb-4">
            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
            </svg>
        </div>
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
            #{{ $tag->name }}
        </h1>
        @if($tag->description)
        <p class="text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
            {{ $tag->description }}
        </p>
        @endif
        <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            {{ $articles->total() }} {{ Str::plural('artículo', $articles->total()) }} etiquetado{{ $articles->total() !== 1 ? 's' : '' }}
        </div>
    </div>

    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                    <svg class="w-3 h-3 mr-2.5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                    </svg>
                    Inicio
                </a>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">
                        Etiqueta: {{ $tag->name }}
                    </span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Main Content -->
        <main class="lg:col-span-3">
            <!-- Articles Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($articles as $article)
                <article class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    @if($article->featured_image)
                    <div class="h-48 overflow-hidden">
                        <img src="{{ Storage::url($article->featured_image) }}" alt="{{ $article->title }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                    </div>
                    @endif
                    <div class="p-6">
                        <div class="flex items-center mb-3">
                            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full" style="background-color: {{ $article->category->color }}20; color: {{ $article->category->color }}">
                                {{ $article->category->name }}
                            </span>
                            <span class="text-gray-500 dark:text-gray-400 text-sm ml-3">
                                {{ $article->published_at->diffForHumans() }}
                            </span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            <a href="{{ route('blog.show', $article->slug) }}">
                                {{ $article->title }}
                            </a>
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">
                            {{ $article->excerpt }}
                        </p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        {{ substr($article->user->name, 0, 1) }}
                                    </span>
                                </div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $article->user->name }}
                                </span>
                            </div>
                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                {{ $article->views_count }}
                            </div>
                        </div>
                        
                        <!-- Tags -->
                        @if($article->tags->count() > 0)
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex flex-wrap gap-1">
                                @foreach($article->tags->take(3) as $articleTag)
                                <a href="{{ route('blog.tag', $articleTag->slug) }}" class="inline-block px-2 py-1 rounded text-xs transition-colors {{ $articleTag->id === $tag->id ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-blue-100 dark:hover:bg-blue-900 hover:text-blue-700 dark:hover:text-blue-300' }}">
                                    {{ $articleTag->name }}
                                </a>
                                @endforeach
                                @if($article->tags->count() > 3)
                                <span class="text-xs text-gray-500 dark:text-gray-400 px-2 py-1">
                                    +{{ $article->tags->count() - 3 }} más
                                </span>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </article>
                @empty
                <div class="col-span-2 text-center py-12">
                    <div class="text-gray-500 dark:text-gray-400">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        <h3 class="text-xl font-semibold mb-2">No hay artículos con esta etiqueta</h3>
                        <p>Explora otras etiquetas o vuelve pronto para ver nuevo contenido.</p>
                    </div>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($articles->hasPages())
            <div class="mt-8">
                {{ $articles->links() }}
            </div>
            @endif
        </main>

        <!-- Sidebar -->
        <aside class="lg:col-span-1">
            <!-- Categories -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Categorías</h3>
                <ul class="space-y-2">
                    @foreach($categories as $category)
                    <li>
                        <a href="{{ route('blog.category', $category->slug) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full mr-3" style="background-color: {{ $category->color }}"></div>
                                <span class="text-gray-700 dark:text-gray-300">{{ $category->name }}</span>
                            </div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $category->articles_count }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            <!-- Popular Tags -->
            @if($popularTags->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Etiquetas Populares</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($popularTags as $popularTag)
                    <a href="{{ route('blog.tag', $popularTag->slug) }}" class="inline-block px-3 py-1 rounded-full text-sm transition-colors {{ $popularTag->id === $tag->id ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 font-semibold' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-blue-100 dark:hover:bg-blue-900 hover:text-blue-700 dark:hover:text-blue-300' }}">
                        {{ $popularTag->name }}
                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">({{ $popularTag->published_articles_count }})</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </aside>
    </div>
</div>
@endsection
@extends('layouts.blog')

@section('title', 'RadioAlgoMas')
@section('meta_description', 'Mantente informado con las últimas noticias locales, deportes, entretenimiento y más en
    RadioAlgoMas.')

@section('content')
    <div class="container mx-auto flex flex-col gap-12 px-4 pb-24 pt-4 sm:pt-6">
        <h1 class="sr-only">{{ config('radio.name') }}: radio en vivo y noticias</h1>

        <x-radio-player />

        @if ($featuredArticles->isNotEmpty())
            <section aria-labelledby="featured-title">
                <h2 id="featured-title" class="mb-6 border-b border-gray-200 pb-2 text-xl font-bold text-brand dark:border-gray-700 dark:text-white">
                    Noticias destacadas
                </h2>
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                    <x-news-card :article="$featuredArticles->first()" variant="lead"
                        class="lg:col-span-7 lg:border-r lg:border-gray-200 lg:pr-8 dark:lg:border-gray-700" />
                    @if ($featuredArticles->count() > 1)
                        <div class="flex flex-col divide-y divide-gray-200 lg:col-span-5 dark:divide-gray-700">
                            @foreach ($featuredArticles->skip(1) as $article)
                                <x-news-card :article="$article" variant="side" class="py-6 first:pt-0" />
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        @endif

        <div class="grid grid-cols-1 gap-8 border-t border-gray-200 pt-10 lg:grid-cols-12 dark:border-gray-700">
            <section class="flex flex-col gap-8 lg:col-span-9" aria-labelledby="latest-title">
                <form method="GET" action="{{ route('blog.index') }}" role="search"
                    class="flex flex-col gap-3 border border-gray-200 bg-gray-50 p-4 sm:flex-row dark:border-gray-700 dark:bg-gray-800">
                    <label for="home-search" class="sr-only">Buscar noticias</label>
                    <input id="home-search" type="search" name="search" value="{{ request('search') }}" placeholder="Buscar noticias…"
                        class="w-full flex-1 border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <div class="flex gap-2">
                        <button type="submit" class="radioalgomas-btn-primary cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                            Buscar
                        </button>
                        @if (request()->hasAny(['search', 'category', 'tag']))
                            <a href="{{ route('blog.index') }}" class="radioalgomas-btn-secondary">Limpiar</a>
                        @endif
                    </div>
                </form>

                <h2 id="latest-title" class="border-b border-gray-200 pb-2 text-xl font-bold text-brand dark:border-gray-700 dark:text-white">
                    Últimas noticias
                </h2>

                @if ($articles->isNotEmpty())
                    <div class="grid grid-cols-1 border-l border-t border-gray-200 md:grid-cols-3 dark:border-gray-700">
                        @foreach ($articles as $article)
                            <x-news-card :article="$article" class="border-b border-r border-gray-200 dark:border-gray-700" />
                        @endforeach
                    </div>
                @else
                    <div class="py-16 text-center text-gray-500 dark:text-gray-400">
                        <h3 class="mb-3 text-2xl font-semibold text-gray-900 dark:text-white">No se encontraron artículos</h3>
                        <p class="mx-auto mb-6 max-w-md text-lg">Intenta con otros términos de búsqueda o explora nuestras categorías disponibles.</p>
                        <div class="flex flex-col justify-center gap-3 sm:flex-row">
                            <a href="{{ route('blog.index') }}" class="radioalgomas-btn-primary">Ver todos los artículos</a>
                            <button type="button" onclick="document.getElementById('home-search').focus()" class="radioalgomas-btn-secondary cursor-pointer">
                                Nueva búsqueda
                            </button>
                        </div>
                    </div>
                @endif

                @if ($articles->hasPages())
                    <div>{{ $articles->links() }}</div>
                @endif
            </section>

            <aside class="flex flex-col gap-8 lg:col-span-3">
                <section aria-labelledby="categories-title" class="border border-gray-200 p-5 dark:border-gray-700">
                    <h2 id="categories-title" class="mb-2 border-b border-gray-200 pb-3 text-base font-bold text-brand dark:border-gray-700 dark:text-white">Categorías</h2>
                    <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-700">
                        @foreach ($categories as $category)
                            <li>
                                <a href="{{ route('blog.category', $category->slug) }}"
                                    class="flex items-center justify-between gap-2 py-2.5 text-gray-700 hover:text-brand focus-visible:outline-2 focus-visible:outline-brand dark:text-gray-300 dark:hover:text-white">
                                    <span class="flex items-center gap-2.5">
                                        <span class="size-2 shrink-0 rounded-full" style="background-color: {{ $category->color }}" aria-hidden="true"></span>
                                        {{ $category->name }}
                                    </span>
                                    <span class="bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $category->articles_count }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>

                @if ($popularTags->count() > 0)
                    <section aria-labelledby="tags-title" class="border border-gray-200 p-5 dark:border-gray-700">
                        <h2 id="tags-title" class="mb-4 border-b border-gray-200 pb-3 text-base font-bold text-brand dark:border-gray-700 dark:text-white">Etiquetas populares</h2>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($popularTags as $tag)
                                <a href="{{ route('blog.tag', $tag->slug) }}"
                                    class="bg-gray-100 px-2.5 py-1 text-xs text-gray-700 transition-colors hover:bg-brand hover:text-white focus-visible:outline-2 focus-visible:outline-brand dark:bg-gray-700 dark:text-gray-300">
                                    {{ $tag->name }}
                                    <span class="sr-only">({{ $tag->published_articles_count }} artículos)</span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                <x-ad-banner location="sidebar_home" />
            </aside>
        </div>
    </div>
@endsection

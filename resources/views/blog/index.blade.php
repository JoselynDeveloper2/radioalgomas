@extends('layouts.blog')

@section('title', 'TuCanalTV - Noticias y Actualidad')
@section('meta_description', 'Mantente informado con las últimas noticias locales, deportes, entretenimiento y más en
    TuCanalTV.')

@section('content')
    <div class="container mx-auto px-4 pb-8">
        <!-- Reproductor de Video en Vivo -->
        <section class="mb-8">
            <x-video-player />
        </section>

        <!-- Hero Section con Artículos Destacados -->
        @if ($featuredArticles->count() > 0)
            <section class="mb-12">
                <div class="tucanaltv-featured-title">
                    <h2>Noticias Destacadas</h2>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    @foreach ($featuredArticles as $index => $article)
                        <x-article-card :article="$article" :layout="$index === 0 ? 'featured' : 'default'" :show-author="true" :show-excerpt="true"
                            :show-category="true" />
                    @endforeach
                </div>
            </section>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Contenido Principal -->
            <main class="lg:col-span-3">
                <!-- Filtros y Búsqueda -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
                    <form method="GET" action="{{ route('blog.index') }}" class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Buscar noticias..."
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="tucanaltv-btn-primary">
                                Buscar
                            </button>
                            @if (request()->hasAny(['search', 'category', 'tag']))
                                <a href="{{ route('blog.index') }}" class="tucanaltv-btn-secondary">
                                    Limpiar
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <!-- Lista de Artículos -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @forelse($articles as $article)
                        <x-article-card :article="$article" layout="default" :show-author="true" :show-excerpt="true"
                            :show-category="true" />
                    @empty
                        <div class="col-span-2 text-center py-16">
                            <div class="text-gray-500 dark:text-gray-400">
                                <div
                                    class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-semibold mb-3 text-gray-900 dark:text-white">No se encontraron
                                    artículos</h3>
                                <p class="text-lg mb-6 max-w-md mx-auto">Intenta con otros términos de búsqueda o explora
                                    nuestras categorías disponibles.</p>
                                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                    <a href="{{ route('blog.index') }}" class="tucanaltv-btn-primary">
                                        Ver todos los artículos
                                    </a>
                                    <button
                                        onclick="document.getElementById('searchBar').classList.remove('hidden'); document.querySelector('#searchBar input').focus();"
                                        class="tucanaltv-btn-secondary">
                                        Nueva búsqueda
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Paginación -->
                @if ($articles->hasPages())
                    <div class="mt-8">
                        {{ $articles->links() }}
                    </div>
                @endif
            </main>

            <!-- Sidebar -->
            <aside class="lg:col-span-1">
                <!-- Categorías -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Categorías</h3>
                    <ul class="space-y-2">
                        @foreach ($categories as $category)
                            <li>
                                <a href="{{ route('blog.category', $category->slug) }}"
                                    class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 rounded-full mr-3"
                                            style="background-color: {{ $category->color }}"></div>
                                        <span class="text-gray-700 dark:text-gray-300">{{ $category->name }}</span>
                                    </div>
                                    <span
                                        class="text-sm text-gray-500 dark:text-gray-400">{{ $category->articles_count }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Etiquetas Populares -->
                @if ($popularTags->count() > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Etiquetas Populares</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($popularTags as $tag)
                                <a href="{{ route('blog.tag', $tag->slug) }}"
                                    class="inline-block px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-sm hover:bg-blue-100 dark:hover:bg-blue-900 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                                    {{ $tag->name }}
                                    <span
                                        class="text-xs text-gray-500 dark:text-gray-400 ml-1">({{ $tag->articles_count }})</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>
@endsection

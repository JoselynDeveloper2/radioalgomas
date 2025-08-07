@extends('layouts.blog')

@section('title', $category->meta_title ?: $category->name . ' - ' . config('app.name'))
@section('meta_description', $category->meta_description ?: 'Noticias de ' . $category->name . ' en ' . config('app.name'))
@section('meta_keywords', $category->meta_keywords ?: $category->name . ', noticias, actualidad')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Category Header -->
    <div class="text-center mb-12">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4" style="background-color: {{ $category->color }}20">
            @if($category->icon)
                @svg('heroicon-o-' . $category->icon, 'w-6 h-6', ['style' => 'color: ' . $category->color])
            @else
            <div class="w-8 h-8 rounded-full" style="background-color: {{ $category->color }}"></div>
            @endif
        </div>
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
            {{ $category->name }}
        </h1>
        @if($category->description)
        <p class="text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
            {{ $category->description }}
        </p>
        @endif
        <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            {{ $articles->total() }} {{ Str::plural('artículo', $articles->total()) }}
        </div>
    </div>

    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <x-breadcrumb :items="[
            [
                'label' => $category->name,
                'url' => null
            ]
        ]" />
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Main Content -->
        <main class="lg:col-span-3">
            <!-- Articles Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($articles as $article)
                    <x-article-card
                        :article="$article"
                        layout="default"
                        :show-author="true"
                        :show-excerpt="true"
                        :show-category="false"
                    />
                @empty
                <div class="col-span-2 text-center py-12">
                    <div class="text-gray-500 dark:text-gray-400">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="text-xl font-semibold mb-2">No hay artículos en esta categoría</h3>
                        <p>Vuelve pronto para ver nuevo contenido.</p>
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
            <!-- Other Categories -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Otras Categorías</h3>
                <ul class="space-y-2">
                    @foreach($categories->where('id', '!=', $category->id) as $otherCategory)
                    <li>
                        <a href="{{ route('blog.category', $otherCategory->slug) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full mr-3" style="background-color: {{ $otherCategory->color }}"></div>
                                <span class="text-gray-700 dark:text-gray-300">{{ $otherCategory->name }}</span>
                            </div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $otherCategory->articles_count }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            <!-- Category Stats -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Estadísticas</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Total de artículos</span>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ $articles->total() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Última actualización</span>
                        <span class="font-semibold text-gray-900 dark:text-white">
                            @if($articles->count() > 0)
                            {{ $articles->first()->published_at->diffForHumans() }}
                            @else
                            N/A
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection

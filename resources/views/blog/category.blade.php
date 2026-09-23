@extends('layouts.blog')

@section('title', $category->meta_title ?: $category->name . ' - ' . config('app.name'))
@section('meta_description', $category->meta_description ?: 'Noticias de ' . $category->name . ' en ' . config('app.name'))
@section('meta_keywords', $category->meta_keywords ?: $category->name . ', noticias, actualidad')

@php
    $lineup = \App\Models\RadioShow::lineup(3);
    $lead = $articles->onFirstPage() ? $articles->first() : null;
    $gridArticles = $lead ? $articles->skip(1) : $articles;
@endphp

@section('content')
    <x-radio-bar :lineup="$lineup" />

    <div class="container mx-auto flex flex-col gap-10 px-4 pb-24 pt-6 sm:pt-8">
        <header class="border-b border-gray-200 pb-4 dark:border-gray-700">
            <x-breadcrumb :items="[['label' => $category->name, 'url' => null]]" />
            <h1 class="mt-3 flex items-center gap-3 text-3xl font-bold tracking-tight text-brand sm:text-4xl dark:text-white">
                <span class="h-8 w-1.5 shrink-0" style="background-color: {{ $category->color }}" aria-hidden="true"></span>
                {{ $category->name }}
            </h1>
            <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                @if ($category->description)
                    <p class="text-gray-600 dark:text-gray-300">{{ $category->description }}</p>
                @endif
                <p class="shrink-0 text-xs text-gray-500 sm:ml-auto dark:text-gray-400">
                    {{ $articles->total() }} {{ Str::plural('artículo', $articles->total()) }}
                    @if ($lead)
                        <span aria-hidden="true">·</span> Actualizado {{ $lead->published_at->diffForHumans() }}
                    @endif
                </p>
            </div>
        </header>

        @if ($lead)
            <x-news-card :article="$lead" variant="feature" :show-category="false" />
        @endif

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
            <section class="flex flex-col gap-8 lg:col-span-8" aria-label="Noticias de {{ $category->name }}">
                @if ($gridArticles->isNotEmpty())
                    <div class="grid grid-cols-1 border-l border-t border-gray-200 md:grid-cols-3 dark:border-gray-700">
                        @foreach ($gridArticles as $article)
                            <x-news-card :article="$article" :show-category="false" class="border-b border-r border-gray-200 dark:border-gray-700" />
                        @endforeach
                    </div>
                @elseif ($articles->isEmpty())
                    <div class="border border-gray-200 py-16 text-center dark:border-gray-700">
                        <h2 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">No hay artículos en esta categoría</h2>
                        <p class="text-gray-500 dark:text-gray-400">Vuelve pronto para ver nuevo contenido.</p>
                    </div>
                @endif

                @if ($articles->hasPages())
                    <div>{{ $articles->links() }}</div>
                @endif
            </section>

            <aside class="flex flex-col gap-8 lg:col-span-4">
                @php($otherCategories = $categories->where('id', '!=', $category->id))
                @if ($otherCategories->isNotEmpty())
                    <section aria-labelledby="other-categories-title" class="border border-gray-200 p-5 dark:border-gray-700">
                        <h2 id="other-categories-title" class="mb-2 border-b border-gray-200 pb-3 text-base font-bold text-brand dark:border-gray-700 dark:text-white">Otras categorías</h2>
                        <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-700">
                            @foreach ($otherCategories as $otherCategory)
                                <li>
                                    <a href="{{ route('blog.category', $otherCategory->slug) }}"
                                        class="flex items-center justify-between gap-2 py-2.5 text-gray-700 hover:text-brand focus-visible:outline-2 focus-visible:outline-brand dark:text-gray-300 dark:hover:text-white">
                                        <span class="flex items-center gap-2.5">
                                            <span class="size-2 shrink-0 rounded-full" style="background-color: {{ $otherCategory->color }}" aria-hidden="true"></span>
                                            {{ $otherCategory->name }}
                                        </span>
                                        <span class="bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $otherCategory->articles_count }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <section aria-labelledby="schedule-title" class="border border-gray-200 bg-slate-50 p-5 dark:border-gray-700 dark:bg-gray-800">
                    <h2 id="schedule-title" class="mb-4 border-b border-gray-200 pb-3 text-base font-bold text-brand dark:border-gray-700 dark:text-white">Programación de hoy</h2>
                    <ol class="flex flex-col gap-3 text-sm">
                        @foreach ($lineup['upcoming'] as $item)
                            <li @if ($item === $lineup['current']) aria-current="true" @endif>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $item['start'] }} – {{ $item['end'] }}
                                    @if ($item === $lineup['current'])
                                        <span class="ml-1 font-semibold text-brand dark:text-white">· Ahora</span>
                                    @endif
                                </p>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $item['name'] }}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $item['host'] }}</p>
                            </li>
                        @endforeach
                    </ol>
                    <a href="{{ route('home') }}" class="radioalgomas-btn-primary mt-5 flex justify-center focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                        Escuchar en vivo
                    </a>
                </section>

                <x-ad-banner location="sidebar_category" />
            </aside>
        </div>
    </div>
@endsection

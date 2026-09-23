@extends('layouts.blog')

@section('title', $article->seo_title ?: $article->meta_title ?: $article->title)
@section('meta_description', $article->seo_meta_description ?: $article->meta_description ?: $article->excerpt)
@section('meta_keywords', $article->meta_keywords)
@section('canonical_url', $article->seo_canonical_url ?: $article->canonical_url ?: route('blog.show', $article->slug))

@section('og_type', 'article')
@section('og_title', $article->og_title ?: $article->seo_title ?: $article->title)
@section('og_description', $article->og_description ?: $article->seo_meta_description ?: $article->excerpt)
@section('og_image', $article->og_image ? Storage::url($article->og_image) : ($article->featured_image ?
    Storage::url($article->featured_image) : ''))

@section('twitter_title', $article->og_title ?: $article->seo_title ?: $article->title)
@section('twitter_description', $article->og_description ?: $article->seo_meta_description ?: $article->excerpt)
@section('twitter_image', $article->og_image ? Storage::url($article->og_image) : ($article->featured_image ?
    Storage::url($article->featured_image) : ''))

    @push('schema')
        <x-schema-markup :article="$article" />
    @endpush

@php
    $url = route('blog.show', $article->slug);
    $author = $article->user->name ?? 'Redacción';
    $initials = mb_strtoupper(collect(explode(' ', trim($author)))->filter()->take(2)->map(fn ($word) => mb_substr($word, 0, 1))->join(''));
    $shareLink = 'rounded p-1.5 text-gray-500 hover:text-brand focus-visible:outline-2 focus-visible:outline-brand dark:text-gray-400 dark:hover:text-white';
@endphp

@section('content')
    <x-radio-bar />

    <article class="pb-8">
        <header class="mx-auto max-w-[760px] px-4 pb-6 pt-8 sm:pt-10">
            <x-breadcrumb :items="[
                ['label' => $article->category->name, 'url' => route('blog.category', $article->category->slug)],
                ['label' => Str::limit($article->title, 40), 'url' => null],
            ]" />

            <a href="{{ route('blog.category', $article->category->slug) }}"
                class="mt-4 inline-block border-l-4 pl-3 text-sm font-semibold text-brand hover:underline focus-visible:outline-2 focus-visible:outline-brand dark:text-white"
                style="border-color: {{ $article->category->color }}">
                {{ $article->category->name }}
            </a>

            <h1 class="mt-4 text-3xl font-bold leading-tight tracking-tight text-brand sm:text-4xl lg:text-[42px] lg:leading-[48px] dark:text-white">
                {{ $article->title }}
            </h1>

            @if ($article->excerpt)
                <p class="mt-5 text-lg leading-relaxed text-gray-600 sm:text-xl dark:text-gray-300">{{ $article->excerpt }}</p>
            @endif

            <div class="mt-6 flex flex-col justify-between gap-3 border-y border-gray-200 py-3 sm:flex-row sm:items-center dark:border-gray-700">
                <p class="flex flex-wrap items-center gap-x-2.5 gap-y-1 text-sm text-gray-500 dark:text-gray-400">
                    <span class="flex size-7 items-center justify-center bg-slate-100 text-[11px] font-bold text-brand dark:bg-gray-700 dark:text-white" aria-hidden="true">{{ $initials }}</span>
                    <span class="font-medium text-gray-900 dark:text-white">Por {{ $author }}</span>
                    <span aria-hidden="true">·</span>
                    <time datetime="{{ $article->published_at->toIso8601String() }}">{{ $article->published_at->translatedFormat('j M Y') }}</time>
                    <span aria-hidden="true">·</span>
                    <span>{{ $article->reading_time }} min de lectura</span>
                    <span aria-hidden="true">·</span>
                    <span>{{ number_format($article->views_count, 0, ',', '.') }} vistas</span>
                </p>

                <div class="flex items-center gap-1" role="group" aria-label="Compartir artículo">
                    <a href="https://wa.me/?text={{ urlencode($article->title . ' ' . $url) }}" target="_blank" rel="noopener" aria-label="Compartir en WhatsApp" class="{{ $shareLink }}">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3.5 20.5 5 16a8.5 8.5 0 1 1 3 3Z"/><path d="M9 9.5c.3 2 2.5 4.2 4.5 4.5l1.2-1.1 1.8.9-.4 1.6c-3.9.3-8.4-4.2-8.1-8.1l1.6-.4.9 1.8Z"/></svg>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}" target="_blank" rel="noopener" aria-label="Compartir en Facebook" class="{{ $shareLink }}">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h-2.5A3.5 3.5 0 0 0 9 6.5V10H6.5v3.5H9V21h3.5v-7.5H15l.5-3.5h-3V7a1 1 0 0 1 1-1H15Z"/></svg>
                    </a>
                    <a href="https://twitter.com/intent/tweet?text={{ urlencode($article->title) }}&url={{ urlencode($url) }}" target="_blank" rel="noopener" aria-label="Compartir en X" class="{{ $shareLink }}">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m4 4 16 16M20 4 4 20"/></svg>
                    </a>
                    <button type="button" data-copy-link="{{ $url }}" aria-label="Copiar enlace" class="{{ $shareLink }} cursor-pointer">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"/><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/></svg>
                    </button>
                    <span data-copy-feedback class="text-xs text-gray-500 dark:text-gray-400" role="status"></span>
                </div>
            </div>
        </header>

        @if ($article->featured_image)
            <figure class="mx-auto my-6 max-w-[1080px] px-4">
                <img src="{{ Storage::url($article->featured_image) }}" alt="{{ $article->title }}"
                    class="aspect-[16/9] w-full border border-gray-200 bg-gray-100 object-cover dark:border-gray-700 dark:bg-gray-800">
            </figure>
        @endif

        <div class="prose prose-lg mx-auto max-w-[760px] px-4 py-4 text-gray-800 prose-headings:text-brand prose-a:text-brand prose-blockquote:border-brand prose-blockquote:bg-slate-50 prose-blockquote:py-2 prose-blockquote:font-medium prose-blockquote:text-brand dark:prose-invert dark:text-gray-200 dark:prose-headings:text-white dark:prose-blockquote:bg-gray-800 dark:prose-blockquote:text-white">
            {!! $article->content !!}
        </div>

        <footer class="mx-auto mt-10 flex max-w-[760px] flex-col gap-8 px-4">
            @if ($article->tags->isNotEmpty())
                <div class="flex flex-wrap items-center gap-2 border-t border-gray-200 pt-6 dark:border-gray-700">
                    <span class="mr-1 text-xs font-semibold text-gray-500 dark:text-gray-400">Etiquetas:</span>
                    @foreach ($article->tags as $tag)
                        <a href="{{ route('blog.tag', $tag->slug) }}"
                            class="border border-gray-200 bg-slate-50 px-3 py-1 text-sm font-medium text-brand hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-brand dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            {{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            <section aria-labelledby="author-heading" class="flex flex-col items-start gap-5 border border-gray-200 p-6 sm:flex-row sm:items-center dark:border-gray-700">
                <span class="flex size-16 shrink-0 items-center justify-center border border-gray-200 bg-slate-100 text-lg font-bold text-brand dark:border-gray-700 dark:bg-gray-700 dark:text-white" aria-hidden="true">{{ $initials }}</span>
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Sobre el autor</p>
                    <h2 id="author-heading" class="mt-0.5 text-lg font-bold text-brand dark:text-white">{{ $author }}</h2>
                    @if ($article->user?->bio)
                        <p class="mt-1 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $article->user->bio }}</p>
                    @endif
                </div>
            </section>

            <x-ad-banner location="sidebar_article" />
        </footer>
    </article>

    @if ($relatedArticles->isNotEmpty())
        <section aria-labelledby="more-news-title" class="border-t border-gray-200 bg-slate-50 dark:border-gray-700 dark:bg-gray-800/40">
            <div class="container mx-auto px-4 py-12 sm:py-14">
                <div class="mb-8 flex items-center justify-between border-b border-gray-200 pb-3 dark:border-gray-700">
                    <h2 id="more-news-title" class="text-2xl font-bold text-brand dark:text-white">Más noticias</h2>
                    <a href="{{ route('blog.index') }}" class="text-sm font-semibold text-gray-600 hover:text-brand focus-visible:outline-2 focus-visible:outline-brand dark:text-gray-300 dark:hover:text-white">
                        Ver todas las publicaciones <span aria-hidden="true">→</span>
                    </a>
                </div>
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4 lg:gap-0 lg:divide-x lg:divide-gray-200 dark:lg:divide-gray-700">
                    @foreach ($relatedArticles as $related)
                        <x-news-card :article="$related" variant="compact" class="lg:px-5 lg:first:pl-0 lg:last:pr-0" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection

@push('scripts')
    <script>
        document.querySelector('[data-copy-link]')?.addEventListener('click', async (event) => {
            const feedback = document.querySelector('[data-copy-feedback]');
            try {
                await navigator.clipboard.writeText(event.currentTarget.dataset.copyLink);
                feedback.textContent = 'Enlace copiado';
            } catch {
                feedback.textContent = 'No se pudo copiar';
            }
            setTimeout(() => (feedback.textContent = ''), 2000);
        });
    </script>
@endpush

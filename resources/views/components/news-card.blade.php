@props(['article', 'variant' => 'grid']) {{-- lead | side | grid --}}

@php
    $url = route('blog.show', $article->slug);
    $category = $article->category->name ?? null;
    $author = $article->user->name ?? 'Redacción';
    $date = $article->published_at;

    $imageBox = match ($variant) {
        'lead' => 'aspect-[16/9]',
        'side' => 'aspect-[16/10] sm:w-44 sm:shrink-0',
        default => 'aspect-[16/10]',
    };
    $title = match ($variant) {
        'lead' => 'text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight',
        'side' => 'text-lg font-semibold',
        default => 'text-lg font-bold',
    };
@endphp

<article {{ $attributes->class([
    'group flex',
    'flex-col gap-4' => $variant === 'lead',
    'flex-col gap-4 sm:flex-row' => $variant === 'side',
    'h-full flex-col p-4' => $variant === 'grid',
]) }}>
    <a href="{{ $url }}" tabindex="-1" aria-hidden="true" class="block overflow-hidden bg-gray-100 dark:bg-gray-800 {{ $imageBox }}">
        @if ($article->featured_image)
            <img src="{{ Storage::url($article->featured_image) }}" alt="" loading="{{ $variant === 'lead' ? 'eager' : 'lazy' }}"
                class="size-full object-cover transition-transform duration-500 group-hover:scale-[1.02]">
        @else
            <div class="flex size-full items-center justify-center text-brand-muted">
                <svg class="size-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            </div>
        @endif
    </a>

    <div @class(['flex flex-col', 'gap-3' => $variant === 'lead', 'gap-1.5' => $variant === 'side', 'flex-1 gap-1.5 pt-3' => $variant === 'grid'])>
        @if ($variant === 'lead')
            <p class="flex flex-wrap items-center gap-x-2 text-xs text-gray-500 dark:text-gray-400">
                @if ($category)<span class="text-sm font-semibold text-brand dark:text-white">{{ $category }}</span><span aria-hidden="true">·</span>@endif
                <time datetime="{{ $date->toIso8601String() }}">{{ $date->diffForHumans() }}</time>
                <span aria-hidden="true">·</span>
                <span>Por {{ $author }}</span>
            </p>
        @elseif ($category)
            <p class="text-xs font-semibold text-brand dark:text-gray-300">{{ $category }}</p>
        @endif

        <h3 class="leading-snug text-gray-900 dark:text-white {{ $title }}">
            <a href="{{ $url }}" class="hover:text-brand focus-visible:outline-2 focus-visible:outline-brand dark:hover:text-gray-300">{{ $article->title }}</a>
        </h3>

        @if ($article->excerpt)
            <p @class([
                'text-gray-600 dark:text-gray-400',
                'text-base leading-relaxed line-clamp-3' => $variant === 'lead',
                'text-sm line-clamp-2' => $variant === 'side',
                'text-sm line-clamp-3' => $variant === 'grid',
            ])>{{ $article->excerpt }}</p>
        @endif

        @if ($variant === 'side')
            <p class="text-xs text-gray-500 dark:text-gray-400">Por {{ $author }} · <time datetime="{{ $date->toIso8601String() }}">{{ $date->diffForHumans() }}</time></p>
        @elseif ($variant === 'grid')
            <p class="mt-auto flex justify-between gap-2 border-t border-gray-200 pt-3 text-xs text-gray-500 dark:border-gray-700 dark:text-gray-400">
                <span class="truncate">Por {{ $author }}</span>
                <time datetime="{{ $date->toIso8601String() }}" class="shrink-0">{{ $date->diffForHumans() }}</time>
            </p>
        @endif
    </div>
</article>

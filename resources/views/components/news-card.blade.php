@props(['article', 'variant' => 'grid', 'showCategory' => true]) {{-- lead | side | grid | feature | compact --}}

@php
    $url = route('blog.show', $article->slug);
    $category = $showCategory ? ($article->category->name ?? null) : null;
    $author = $article->user->name ?? 'Redacción';
    $date = $article->published_at;

    $imageBox = match ($variant) {
        'lead' => 'aspect-[16/9]',
        'side' => 'aspect-[16/10] sm:w-44 sm:shrink-0',
        'feature' => 'aspect-[16/9] lg:col-span-7 lg:aspect-auto lg:min-h-[420px]',
        default => 'aspect-[16/10]',
    };
    $title = match ($variant) {
        'lead' => 'text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight',
        'feature' => 'text-2xl lg:text-3xl font-bold tracking-tight',
        'side' => 'text-lg font-semibold',
        'compact' => 'text-base font-bold line-clamp-2',
        default => 'text-lg font-bold',
    };
    $eager = in_array($variant, ['lead', 'feature']);
@endphp

<article {{ $attributes->class([
    'group',
    'flex flex-col gap-4' => $variant === 'lead',
    'flex flex-col gap-4 sm:flex-row' => $variant === 'side',
    'flex h-full flex-col p-4' => $variant === 'grid',
    'flex h-full flex-col' => $variant === 'compact',
    'grid grid-cols-1 border border-gray-200 lg:grid-cols-12 dark:border-gray-700' => $variant === 'feature',
]) }}>
    {{-- La imagen va absoluta: la caja define el alto, así una foto vertical no estira la tarjeta. --}}
    <a href="{{ $url }}" tabindex="-1" aria-hidden="true" class="relative block overflow-hidden bg-gray-100 dark:bg-gray-800 {{ $imageBox }}">
        @if ($article->featured_image)
            <img src="{{ Storage::url($article->featured_image) }}" alt="" loading="{{ $eager ? 'eager' : 'lazy' }}"
                class="absolute inset-0 size-full object-cover object-[50%_25%] transition-transform duration-500 group-hover:scale-[1.02]">
        @else
            <div class="absolute inset-0 flex items-center justify-center text-brand-muted">
                <svg class="size-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            </div>
        @endif
    </a>

    <div @class([
        'flex flex-col',
        'gap-3' => $variant === 'lead',
        'gap-1.5' => $variant === 'side',
        'flex-1 gap-1.5 pt-3' => in_array($variant, ['grid', 'compact']),
        'gap-3 border-t border-gray-200 p-6 lg:col-span-5 lg:border-l lg:border-t-0 lg:p-8 dark:border-gray-700' => $variant === 'feature',
    ])>
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

        @if ($article->excerpt && $variant !== 'compact')
            <p @class([
                'text-gray-600 dark:text-gray-400',
                'text-base leading-relaxed line-clamp-3' => $variant === 'lead',
                'text-base leading-relaxed line-clamp-4' => $variant === 'feature',
                'text-sm line-clamp-2' => $variant === 'side',
                'text-sm line-clamp-3' => $variant === 'grid',
            ])>{{ $article->excerpt }}</p>
        @endif

        @if ($variant === 'compact')
            <time datetime="{{ $date->toIso8601String() }}" class="mt-auto text-xs text-gray-500 dark:text-gray-400">{{ $date->diffForHumans() }}</time>
        @elseif ($variant === 'side')
            <p class="text-xs text-gray-500 dark:text-gray-400">Por {{ $author }} · <time datetime="{{ $date->toIso8601String() }}">{{ $date->diffForHumans() }}</time></p>
        @elseif (in_array($variant, ['grid', 'feature']))
            <p @class([
                'mt-auto flex justify-between gap-2 border-t border-gray-200 text-xs text-gray-500 dark:border-gray-700 dark:text-gray-400',
                'pt-3' => $variant === 'grid',
                'pt-4' => $variant === 'feature',
            ])>
                <span class="truncate">Por {{ $author }}</span>
                <time datetime="{{ $date->toIso8601String() }}" class="shrink-0">{{ $date->diffForHumans() }}</time>
            </p>
        @endif
    </div>
</article>

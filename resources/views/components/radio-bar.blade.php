@props(['lineup' => null])

@php
    $show = ($lineup ?? \App\Models\RadioShow::lineup())['show'];
@endphp

<aside aria-label="Radio en vivo" class="bg-brand text-white">
    <div class="container mx-auto flex items-center justify-between gap-4 px-4 py-2 text-sm">
        <p class="flex min-w-0 items-center gap-3">
            <span class="inline-flex shrink-0 items-center gap-2 text-xs font-semibold">
                <span class="radio-live-dot size-2 rounded-full bg-red-500" aria-hidden="true"></span>
                En vivo
            </span>
            <span class="text-white/40" aria-hidden="true">|</span>
            <span class="truncate">
                <span class="font-medium">{{ $show['name'] }}</span>
                <span class="hidden text-white/70 sm:inline">· con {{ $show['host'] }}</span>
            </span>
        </p>
        <a href="{{ route('home') }}"
            class="inline-flex shrink-0 items-center gap-2 bg-white px-3 py-1 text-xs font-semibold text-brand hover:bg-white/90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
            <span class="flex size-5 items-center justify-center rounded-full bg-brand text-white" aria-hidden="true">
                <svg class="size-3" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.14v13.72a1 1 0 0 0 1.5.86l11-6.86a1 1 0 0 0 0-1.72l-11-6.86A1 1 0 0 0 8 5.14Z"/></svg>
            </span>
            Escuchar
        </a>
    </div>
</aside>

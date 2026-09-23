@php
    ['settings' => $settings, 'current' => $current, 'show' => $show, 'next' => $next, 'upcoming' => $upcoming] = \App\Models\RadioShow::lineup();
@endphp

<section
    data-radio-player
    data-state="idle"
    data-autoplay="{{ $settings->autoplay ? 'true' : 'false' }}"
    data-stream="{{ $settings->stream_url }}"
    data-station="{{ $settings->station_name }}"
    data-show="{{ $show['name'] }}"
    data-artwork="{{ asset('apple-touch-icon.png') }}"
    aria-labelledby="radio-show-title"
    class="group relative overflow-hidden rounded-xl bg-brand p-5 text-white sm:p-8 lg:p-12">
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-12 lg:items-center lg:gap-12">
        <div class="flex flex-col gap-6 sm:gap-8 lg:col-span-8">
            <div class="flex flex-col gap-2 sm:gap-3">
                <div class="flex items-center gap-3 text-xs">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 font-medium">
                        <span class="radio-live-dot size-2 rounded-full bg-red-500 group-data-[state=playing]:bg-emerald-400" aria-hidden="true"></span>
                        En vivo
                    </span>
                    <span class="text-white/70">{{ $settings->station_name }}</span>
                </div>
                <h2 id="radio-show-title" class="text-3xl font-bold leading-tight tracking-tight sm:text-4xl lg:text-5xl">
                    {{ $show['name'] }}
                </h2>
                <p class="text-sm text-white/70 sm:text-base">
                    con {{ $show['host'] }}@if ($current) · {{ $current['start'] }} – {{ $current['end'] }}@endif
                </p>
            </div>

            <div class="flex items-center gap-6 sm:gap-10">
                <button type="button" data-radio-toggle aria-label="Reproducir radio en vivo"
                    class="flex size-20 shrink-0 cursor-pointer items-center justify-center rounded-full bg-white text-brand ring-4 ring-white/25 transition hover:scale-105 active:scale-95 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white sm:size-24">
                    <svg class="size-9 group-data-[state=loading]:hidden group-data-[state=playing]:hidden sm:size-11" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.14v13.72a1 1 0 0 0 1.5.86l11-6.86a1 1 0 0 0 0-1.72l-11-6.86A1 1 0 0 0 8 5.14Z"/></svg>
                    <svg class="hidden size-9 group-data-[state=playing]:block sm:size-11" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                    <svg class="hidden size-9 animate-spin group-data-[state=loading]:block sm:size-10" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".2" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                </button>

                <div class="relative h-16 min-w-0 flex-1 sm:h-24" aria-hidden="true">
                    <div class="radio-eq absolute inset-0 flex items-center justify-between gap-[3px]">
                        @for ($i = 0; $i < 32; $i++)
                            <span class="max-w-1.5 min-w-0 flex-1 rounded-full bg-white/70"
                                style="height: {{ round(20 + 75 * abs(sin($i * 0.7) * cos($i * 0.23))) }}%; --i: {{ $i }}"></span>
                        @endfor
                    </div>
                    <canvas data-radio-canvas class="radio-canvas absolute inset-0 size-full"></canvas>
                </div>
            </div>

            <div data-radio-error hidden class="rounded-lg bg-white/10 px-4 py-3 text-sm">
                <div class="flex flex-wrap items-center gap-3">
                    <span>No pudimos conectar con la señal. Revisa tu conexión e inténtalo de nuevo.</span>
                    <button type="button" data-radio-retry
                        class="cursor-pointer rounded-md bg-white px-3 py-1.5 font-semibold text-brand hover:bg-white/90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                        Reintentar
                    </button>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4 border-t border-white/10 pt-4 text-sm text-white/70">
                <div class="flex items-center gap-3">
                    <button type="button" data-radio-mute data-muted="false" aria-label="Silenciar"
                        class="group/mute cursor-pointer rounded p-1 text-white hover:text-white/80 focus-visible:outline-2 focus-visible:outline-white">
                        <svg class="size-5 group-data-[muted=true]/mute:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 5 6 9H2v6h4l5 4V5Z"/><path d="M15.5 8.5a5 5 0 0 1 0 7M19 5a10 10 0 0 1 0 14"/></svg>
                        <svg class="hidden size-5 group-data-[muted=true]/mute:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 5 6 9H2v6h4l5 4V5Z"/><path d="m22 9-6 6M16 9l6 6"/></svg>
                    </button>
                    <input type="range" data-radio-volume min="0" max="100" value="80" aria-label="Volumen"
                        class="hidden w-28 cursor-pointer accent-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white sm:block">
                    <span data-radio-volume-label class="hidden w-9 text-xs tabular-nums sm:inline">80%</span>
                </div>

                <p class="flex items-center gap-2 text-xs">
                    <span class="size-2 rounded-full bg-white/40 group-data-[state=playing]:bg-emerald-400 group-data-[state=error]:bg-red-400" aria-hidden="true"></span>
                    <span data-radio-status role="status">Listo para escuchar</span>
                </p>

                <button type="button" data-radio-share
                    class="flex cursor-pointer items-center gap-1.5 rounded p-1 text-xs hover:text-white focus-visible:outline-2 focus-visible:outline-white">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4"/></svg>
                    <span data-radio-share-label>Compartir</span>
                </button>
            </div>
        </div>

        <aside aria-labelledby="radio-schedule-title" class="rounded-xl border border-white/10 bg-white/5 p-5 sm:p-6 lg:col-span-4">
            <h3 id="radio-schedule-title" class="mb-4 border-b border-white/10 pb-3 text-base font-bold">Programación de hoy</h3>
            <ol class="flex flex-col gap-2">
                @foreach ($upcoming as $item)
                    @php
                        $isNow = $item === $current;
                        $isNext = ! $isNow && $item === $next;
                    @endphp
                    <li class="rounded-r-md border-l-4 py-2 pl-3 pr-2 {{ $isNow ? 'border-white bg-white/10' : 'border-transparent' }}" @if ($isNow) aria-current="true" @endif>
                        <div class="flex items-center justify-between gap-2 text-xs">
                            <span class="{{ $isNow ? 'font-bold text-white' : 'text-white/70' }}">{{ $item['start'] }} – {{ $item['end'] }}</span>
                            @if ($isNow)
                                <span class="rounded-full bg-white px-2 py-0.5 text-[11px] font-semibold text-brand">Ahora</span>
                            @elseif ($isNext)
                                <span class="rounded-full bg-white/10 px-2 py-0.5 text-[11px] text-white/80">A continuación</span>
                            @endif
                        </div>
                        <p class="mt-0.5 text-sm font-semibold">{{ $item['name'] }}</p>
                        <p class="text-xs text-white/70">{{ $item['host'] }}</p>
                    </li>
                @endforeach
            </ol>
        </aside>
    </div>

    <audio preload="none"></audio>
</section>

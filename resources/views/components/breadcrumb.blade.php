@props(['items' => []])

<nav aria-label="Migas de pan">
    <ol class="flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
        <li>
            <a href="{{ route('home') }}" class="hover:text-brand focus-visible:outline-2 focus-visible:outline-brand dark:hover:text-white">Inicio</a>
        </li>
        @foreach ($items as $item)
            <li class="text-gray-300 dark:text-gray-600" aria-hidden="true">/</li>
            <li @if ($loop->last) aria-current="page" class="max-w-xs truncate font-semibold text-gray-900 dark:text-white" @endif>
                @if ($loop->last || empty($item['url']))
                    {{ $item['label'] }}
                @else
                    <a href="{{ $item['url'] }}" class="hover:text-brand focus-visible:outline-2 focus-visible:outline-brand dark:hover:text-white">{{ $item['label'] }}</a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

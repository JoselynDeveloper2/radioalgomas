@props([
    'items' => [],
    'theme' => 'light' // light or dark
])

@php
    $textClasses = $theme === 'dark' 
        ? 'text-blue-100 hover:text-white' 
        : 'text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white';
    
    $separatorClasses = $theme === 'dark'
        ? 'text-blue-200'
        : 'text-gray-400';
        
    $currentClasses = $theme === 'dark'
        ? 'text-blue-200'
        : 'text-gray-500 dark:text-gray-400';
@endphp

<nav class="flex" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2 text-sm">
        <!-- Home link -->
        <li>
            <a href="{{ route('home') }}" class="flex items-center {{ $textClasses }} transition-colors">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                </svg>
                Inicio
            </a>
        </li>
        
        @foreach($items as $index => $item)
            <li>
                <svg class="w-4 h-4 {{ $separatorClasses }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </li>
            <li @if($loop->last) aria-current="page" @endif>
                @if($loop->last)
                    <span class="{{ $currentClasses }} truncate max-w-xs">
                        {{ $item['label'] }}
                    </span>
                @else
                    <a href="{{ $item['url'] }}" class="{{ $textClasses }} transition-colors">
                        {{ $item['label'] }}
                    </a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'RadioAlgoMas'))</title>
    <meta name="description" content="@yield('meta_description', 'RadioAlgoMas - Tu fuente confiable de noticias locales y actualidad')">
    <meta name="keywords" content="@yield('meta_keywords', 'noticias, actualidad, deportes, entretenimiento, política, RadioAlgoMas')">
    <meta name="author" content="RadioAlgoMas">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('og_title', config('app.name'))">
    <meta property="og:description" content="@yield('og_description', 'RadioAlgoMas - Tu fuente confiable de noticias locales y actualidad')">
    <meta property="og:image" content="@yield('og_image', asset('images/og-default.jpg'))">
    <meta property="og:site_name" content="{{ config('app.name') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="@yield('twitter_title', config('app.name'))">
    <meta property="twitter:description" content="@yield('twitter_description', 'RadioAlgoMas - Tu fuente confiable de noticias locales y actualidad')">
    <meta property="twitter:image" content="@yield('twitter_image', asset('images/og-default.jpg'))">

    <!-- Canonical URL -->
    <link rel="canonical" href="@yield('canonical_url', url()->current())">

    <!-- RSS Feed -->
    <link rel="alternate" type="application/rss+xml" title="{{ config('app.name') }} RSS" href="{{ route('blog.rss') }}">

    <!-- Favicon -->
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Schema.org JSON-LD -->
    @stack('schema')

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-JRDQT4H1X1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-JRDQT4H1X1');
    </script>

    <!-- Additional Head Content -->
    @stack('head')
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-900 font-sans antialiased">
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TQHH3HQ8"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-2">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2">
                        <img src="{{ asset('images/radioalgomas-logo-2-ecualizador.svg') }}" alt="RadioAlgoMas Logo" class="h-[70px] w-auto">
                    </a>
                </div>

                <!-- Navigation -->
                <nav class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="text-gray-700 dark:text-gray-300 hover:text-brand dark:hover:text-white transition-colors {{ request()->routeIs('home') ? 'text-brand dark:text-white font-semibold' : '' }}">
                        Inicio
                    </a>
                    @foreach($categories ?? [] as $category)
                    <a href="{{ route('blog.category', $category->slug) }}" class="text-gray-700 dark:text-gray-300 hover:text-brand dark:hover:text-white transition-colors {{ request()->route('slug') === $category->slug ? 'text-brand dark:text-white font-semibold' : '' }}">
                        {{ $category->name }}
                    </a>
                    @endforeach
                </nav>

                <!-- Search & Theme Toggle -->
                <div class="flex items-center space-x-4">
                    <!-- Search Button -->
                    <button type="button" onclick="toggleSearch()" aria-label="Buscar noticias" aria-controls="searchBar" class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>

                    <!-- Theme Toggle -->
                    <button type="button" onclick="toggleTheme()" aria-label="Cambiar tema claro u oscuro" class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>

                    <!-- Mobile Menu Button -->
                    <button type="button" onclick="toggleMobileMenu()" aria-label="Abrir menú" aria-controls="mobileMenu" class="md:hidden p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Search Bar (Hidden by default) -->
            <div id="searchBar" class="hidden pb-4">
                <form method="GET" action="{{ route('blog.index') }}" class="max-w-md mx-auto">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar noticias..." class="w-full px-4 py-2 pl-10 pr-4 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Mobile Menu (Hidden by default) -->
            <div id="mobileMenu" class="hidden md:hidden pb-4">
                <nav class="flex flex-col space-y-2">
                    <a href="{{ route('home') }}" class="px-3 py-2 text-gray-700 dark:text-gray-300 hover:text-brand dark:hover:text-white transition-colors {{ request()->routeIs('home') ? 'text-brand dark:text-white font-semibold' : '' }}">
                        Inicio
                    </a>
                    @foreach($categories ?? [] as $category)
                    <a href="{{ route('blog.category', $category->slug) }}" class="px-3 py-2 text-gray-700 dark:text-gray-300 hover:text-brand dark:hover:text-white transition-colors {{ request()->route('slug') === $category->slug ? 'text-brand dark:text-white font-semibold' : '' }}">
                        {{ $category->name }}
                    </a>
                    @endforeach
                </nav>
            </div>
        </div>
    </header>

    <!-- Header Ad -->
    @if(!request()->routeIs('home'))
    <div class="container mx-auto px-4 mt-6">
        <x-ad-banner location="header" />
    </div>
    @endif

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    @php($footerLineup = \App\Models\RadioShow::lineup())
    <footer class="bg-[#002444] text-white">
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 gap-10 border-b border-white/10 pb-10 md:grid-cols-12">
                <div class="md:col-span-5">
                    <img src="{{ asset('images/radioalgomas-logo-2-ecualizador-blanco.svg') }}" alt="{{ $footerLineup['settings']->station_name }}" class="h-14 w-auto">
                    <p class="mt-4 max-w-md text-sm leading-relaxed text-gray-300">
                        Radio digital y noticias locales. Información, deportes, entretenimiento y música, en vivo por internet las 24 horas.
                    </p>
                </div>

                <nav aria-labelledby="footer-sections" class="md:col-span-3">
                    <h2 id="footer-sections" class="mb-4 text-sm font-bold">Secciones</h2>
                    <ul class="flex flex-col gap-2 text-sm text-gray-300">
                        <li><a href="{{ route('home') }}" class="hover:text-white focus-visible:outline-2 focus-visible:outline-white">Inicio</a></li>
                        @foreach ($categories ?? [] as $category)
                            <li><a href="{{ route('blog.category', $category->slug) }}" class="hover:text-white focus-visible:outline-2 focus-visible:outline-white">{{ $category->name }}</a></li>
                        @endforeach
                    </ul>
                </nav>

                <section aria-labelledby="footer-live" class="md:col-span-4">
                    <h2 id="footer-live" class="mb-4 text-sm font-bold">Escúchanos en vivo</h2>
                    <p class="text-sm text-gray-300">Transmisión digital por internet, desde cualquier navegador y dispositivo.</p>
                    <div class="mt-4 border border-white/15 p-4">
                        <p class="flex items-center gap-2 text-xs text-gray-400">
                            <span class="radio-live-dot size-2 rounded-full bg-red-500" aria-hidden="true"></span>
                            Ahora suena
                        </p>
                        <p class="mt-1 font-semibold">{{ $footerLineup['show']['name'] }}</p>
                        <p class="text-xs text-gray-400">con {{ $footerLineup['show']['host'] }}</p>
                        <a href="{{ route('home') }}"
                            class="mt-4 inline-flex items-center gap-2 bg-white px-3 py-1.5 text-xs font-semibold text-brand hover:bg-white/90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                            <svg class="size-3" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.14v13.72a1 1 0 0 0 1.5.86l11-6.86a1 1 0 0 0 0-1.72l-11-6.86A1 1 0 0 0 8 5.14Z"/></svg>
                            Escuchar ahora
                        </a>
                    </div>
                </section>
            </div>

            <div class="flex flex-col gap-3 pt-6 text-xs text-gray-400 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} {{ $footerLineup['settings']->station_name }}. Todos los derechos reservados.</p>
                <ul class="flex gap-4">
                    <li><a href="{{ route('blog.rss') }}" class="hover:text-white focus-visible:outline-2 focus-visible:outline-white">RSS</a></li>
                    <li><a href="{{ route('sitemap') }}" class="hover:text-white focus-visible:outline-2 focus-visible:outline-white">Mapa del sitio</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        function toggleSearch() {
            const searchBar = document.getElementById('searchBar');
            searchBar.classList.toggle('hidden');
            if (!searchBar.classList.contains('hidden')) {
                searchBar.querySelector('input').focus();
            }
        }

        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('hidden');
        }

        function toggleTheme() {
            const html = document.documentElement;
            html.classList.toggle('dark');
            localStorage.setItem('color-theme', html.classList.contains('dark') ? 'dark' : 'light');
        }

        // Initialize theme - default to light mode
        if (localStorage.getItem('color-theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
        }
    </script>

    @stack('scripts')
</body>
</html>

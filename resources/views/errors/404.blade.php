@extends('layouts.blog')

@section('title', 'Página no encontrada - 404')
@section('meta_description', 'La página que buscas no existe o ha sido movida. Explora nuestro contenido en RadioAlgoMas.')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center px-4 py-12">
    <div class="max-w-4xl mx-auto text-center">
        <!-- Error Number -->
        <div class="mb-8">
            <h1 class="text-9xl md:text-[12rem] font-bold text-transparent bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text leading-none">
                404
            </h1>
        </div>
        
        <!-- Main Message -->
        <div class="mb-8">
            <h2 class="text-2xl md:text-4xl font-bold text-gray-800 mb-4">
                ¡Oops! Página no encontrada
            </h2>
            <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
                La página que estás buscando no existe o ha sido movida. No te preocupes, te ayudamos a encontrar lo que necesitas.
            </p>
        </div>

        <!-- TV Illustration -->
        <div class="mb-12">
            <div class="relative max-w-md mx-auto">
                <svg class="w-64 h-48 mx-auto text-gray-300" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M21 3H3c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h5l-1 2v1h8v-1l-1-2h5c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 12H3V5h18v10z"/>
                    <circle cx="12" cy="10" r="2" fill="currentColor"/>
                    <path d="M12 13c-1.66 0-3 1.34-3 3h6c0-1.66-1.34-3-3-3z"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-gray-400 text-xl font-semibold">Sin señal</div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
            <a href="{{ route('blog.index') }}" 
               class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-lg hover:shadow-xl">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Ir al Inicio
            </a>
            <button onclick="history.back()" 
                    class="inline-flex items-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Volver Atrás
            </button>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-3xl mx-auto">
            <div class="bg-white rounded-xl p-6 shadow-md hover:shadow-lg transition-shadow duration-200">
                <div class="text-blue-600 mb-4">
                    <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">Últimas Noticias</h3>
                <p class="text-sm text-gray-600 mb-4">Mantente informado con nuestras últimas publicaciones</p>
                <a href="{{ route('blog.index') }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm" aria-label="Ver últimas noticias">
                    Ver noticias →
                </a>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-md hover:shadow-lg transition-shadow duration-200">
                <div class="text-green-600 mb-4">
                    <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">Categorías</h3>
                <p class="text-sm text-gray-600 mb-4">Explora nuestro contenido por categorías</p>
                <a href="{{ route('blog.index') }}" class="text-green-600 hover:text-green-800 font-medium text-sm" aria-label="Explorar categorías de contenido">
                    Explorar →
                </a>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-md hover:shadow-lg transition-shadow duration-200">
                <div class="text-purple-600 mb-4">
                    <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">Buscar</h3>
                <p class="text-sm text-gray-600 mb-4">Encuentra lo que estás buscando</p>
                <div class="relative">
                    <label for="error-404-search" class="sr-only">Buscar contenido</label>
                    <input type="text" 
                           id="error-404-search"
                           placeholder="Buscar..." 
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                           onkeypress="if(event.key==='Enter') { window.location.href='{{ route('blog.index') }}?search=' + encodeURIComponent(this.value); }">
                </div>
            </div>
        </div>

        <!-- Contact Info -->
        <div class="mt-12 text-sm text-gray-500">
            <p>¿Necesitas ayuda? Visita nuestra página principal o contacta con nuestro equipo.</p>
            <div class="mt-2 flex justify-center space-x-4">
                <a href="{{ route('blog.index') }}" class="hover:text-blue-600 transition-colors">Inicio</a>
                <span>•</span>
                <a href="mailto:contacto@radioalgomas.com" class="hover:text-blue-600 transition-colors">Contacto</a>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-focus en el campo de búsqueda cuando se presiona '/'
document.addEventListener('keydown', function(e) {
    if (e.key === '/' && !e.ctrlKey && !e.metaKey) {
        e.preventDefault();
        document.querySelector('input[type="text"]').focus();
    }
});
</script>
@endsection

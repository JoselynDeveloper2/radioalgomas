@extends('layouts.blog')

@section('title', 'Error del Servidor - 500')
@section('meta_description', 'Estamos experimentando problemas técnicos. Vuelve en unos minutos.')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-red-50 to-orange-100 flex items-center justify-center px-4 py-12">
    <div class="max-w-2xl mx-auto text-center">
        <!-- Error Number -->
        <div class="mb-8">
            <h1 class="text-9xl font-bold text-transparent bg-gradient-to-r from-red-600 to-orange-600 bg-clip-text">
                500
            </h1>
        </div>
        
        <!-- Main Message -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">
                Error del Servidor
            </h2>
            <p class="text-lg text-gray-600 mb-8">
                Estamos experimentando problemas técnicos temporales. Nuestro equipo ya está trabajando en solucionarlo.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-8">
            <button onclick="window.location.reload()" 
                    class="inline-flex items-center px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors duration-200"
                    aria-label="Recargar la página">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Reintentar
            </button>
            <a href="{{ route('blog.index') }}" 
               class="inline-flex items-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200"
               aria-label="Ir a la página de inicio">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Ir al Inicio
            </a>
        </div>

        <div class="text-sm text-gray-500">
            <p>Si el problema persiste, puedes contactarnos en: 
               <a href="mailto:soporte@radioalgomas.com" class="text-red-600 hover:text-red-800">soporte@radioalgomas.com</a>
            </p>
        </div>
    </div>
</div>
@endsection

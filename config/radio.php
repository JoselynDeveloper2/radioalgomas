<?php

// Valores de respaldo. Desde el administrador (Streaming > Configuración del
// Player y Streaming > Programación) el cliente los sustituye sin tocar código;
// estos solo se usan mientras esa configuración esté vacía (p. ej. recién desplegado).
return [
    'name' => 'Radio Algo Más',

    // Demo: SomaFM Groove Salad (Icecast, MP3, CORS abierto). Reemplazar por el stream del cliente.
    'stream_url' => env('RADIO_STREAM_URL', 'https://ice1.somafm.com/groovesalad-128-mp3'),

    // Intenta arrancar con sonido al cargar. El navegador decide: suele permitirlo a oyentes
    // recurrentes y bloquearlo a visitantes nuevos, que ven el botón de play.
    'autoplay' => env('RADIO_AUTOPLAY', true),

    // Zona horaria de la emisora; la app corre en UTC.
    'timezone' => env('RADIO_TIMEZONE', 'UTC'),

    // Parrilla diaria en hora local de la emisora. Formato HH:MM.
    'schedule' => [
        ['start' => '06:00', 'end' => '10:00', 'name' => 'Mañanas Informativas', 'host' => '[Nombre del conductor]'],
        ['start' => '10:00', 'end' => '13:00', 'name' => 'Mesa de Diálogo', 'host' => '[Nombre del conductor]'],
        ['start' => '13:00', 'end' => '15:30', 'name' => 'Panorama Deportivo', 'host' => '[Nombre del conductor]'],
        ['start' => '15:30', 'end' => '18:00', 'name' => 'Tardes de Radio', 'host' => '[Nombre del conductor]'],
        ['start' => '18:00', 'end' => '22:00', 'name' => 'Noches de Música', 'host' => '[Nombre del conductor]'],
    ],

    // Se muestra fuera de la parrilla.
    'fallback_show' => ['name' => 'Música continua', 'host' => 'Radio Algo Más'],
];

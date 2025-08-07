<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stream Testing Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para las pruebas de conectividad de streams
    |
    */

    'test_timeout' => env('STREAM_TEST_TIMEOUT', 10),
    'connection_timeout' => env('STREAM_CONNECTION_TIMEOUT', 5),
    
    'allowed_content_types' => [
        'video/mp4',
        'application/x-mpegURL',
        'video/x-ms-wmv',
        'video/quicktime',
        'application/octet-stream',
        'video/mp2t',
        'application/vnd.apple.mpegurl',
        'video/x-flv',
        'video/webm',
        'application/dash+xml',
    ],

    /*
    |--------------------------------------------------------------------------
    | Stream Player Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración del reproductor de video
    |
    */

    'player' => [
        'default_controls' => true,
        'autoplay' => false,
        'muted' => false,
        'loop' => false,
        'preload' => 'metadata',
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para el monitoreo de streams
    |
    */

    'monitoring' => [
        'retest_interval_minutes' => env('STREAM_RETEST_INTERVAL', 60),
        'health_check_interval_minutes' => env('STREAM_HEALTH_CHECK_INTERVAL', 15),
        'max_failed_attempts' => env('STREAM_MAX_FAILED_ATTEMPTS', 3),
    ],
];
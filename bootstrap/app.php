<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withSchedule(function ($schedule) {
        // Sistema de rotación: importar una categoría cada minuto (rotando cada 15 min)
        $schedule->command('rss:import --rotate')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/rss-import.log'));
        
        // Comando de respaldo: importar todas las categorías cada 2 horas (por seguridad)
        $schedule->command('rss:import --force')
            ->everyTwoHours()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/rss-import-full.log'));

        // Desactivados a propósito: rss:process-with-delay y news:rewrite-content reescriben con
        // sinónimos, algo que Google trata como contenido manipulado y puede penalizar el dominio.
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

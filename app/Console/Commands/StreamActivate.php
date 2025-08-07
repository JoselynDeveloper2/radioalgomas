<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\PlayerUrl;
use Illuminate\Console\Command;

class StreamActivate extends Command
{
    protected $signature = 'stream:activate 
                          {url? : ID o nombre de la URL a activar}
                          {--test : Probar conectividad antes de activar}';

    protected $description = 'Activar una URL de streaming específica';

    public function handle(): int
    {
        $urlIdentifier = $this->argument('url');
        
        if (!$urlIdentifier) {
            $urlIdentifier = $this->selectUrl();
        }

        $playerUrl = $this->findUrl($urlIdentifier);
        
        if (!$playerUrl) {
            $this->error("❌ No se encontró la URL especificada: {$urlIdentifier}");
            return Command::FAILURE;
        }

        $this->info("🎯 URL encontrada: {$playerUrl->name}");
        $this->line("   URL: {$playerUrl->url}");

        if ($this->option('test')) {
            $this->info("🔍 Probando conectividad...");
            
            $testService = app(\App\Services\StreamTestService::class);
            $result = $testService->testPlayerUrl($playerUrl);
            
            if (!$result['success']) {
                $this->error("❌ La URL falló la prueba de conectividad: {$result['message']}");
                
                if (!$this->confirm('¿Desea activarla de todas formas?')) {
                    return Command::FAILURE;
                }
            } else {
                $this->info("✅ Test exitoso ({$result['response_time']} ms)");
            }
        }

        if ($playerUrl->is_active) {
            $this->warn("⚠️  Esta URL ya está activa");
            return Command::SUCCESS;
        }

        $currentActive = PlayerUrl::getActive();
        if ($currentActive) {
            $this->warn("⚠️  Se desactivará: {$currentActive->name}");
        }

        if ($this->confirm("¿Activar '{$playerUrl->name}'?")) {
            $playerUrl->activate();
            $this->info("✅ URL activada exitosamente");
            
            if ($currentActive) {
                $this->line("   URL anterior desactivada: {$currentActive->name}");
            }
        } else {
            $this->info("Operación cancelada");
        }

        return Command::SUCCESS;
    }

    private function selectUrl(): string
    {
        $urls = PlayerUrl::orderBy('name')->get();
        
        if ($urls->isEmpty()) {
            $this->error('❌ No hay URLs configuradas');
            exit(1);
        }

        $choices = $urls->map(function ($url) {
            $status = $url->is_active ? ' [ACTIVA]' : '';
            return "{$url->id}: {$url->name}{$status}";
        })->toArray();

        $selected = $this->choice('Seleccione la URL a activar:', $choices);
        
        return explode(':', $selected)[0];
    }

    private function findUrl(string $identifier): ?PlayerUrl
    {
        // Buscar por ID
        if (is_numeric($identifier)) {
            return PlayerUrl::find($identifier);
        }

        // Buscar por nombre
        return PlayerUrl::where('name', 'like', "%{$identifier}%")->first();
    }
}
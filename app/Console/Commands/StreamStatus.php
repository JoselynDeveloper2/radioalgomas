<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\PlayerUrl;
use App\Services\StreamTestService;
use Illuminate\Console\Command;

class StreamStatus extends Command
{
    protected $signature = 'stream:status 
                          {--detailed : Mostrar información detallada}
                          {--stats : Mostrar estadísticas de rendimiento}';

    protected $description = 'Mostrar el estado actual de las URLs de streaming';

    public function handle(StreamTestService $testService): int
    {
        $this->info('📊 Estado del Sistema de Streaming');
        $this->newLine();

        $this->showCurrentStatus();

        if ($this->option('detailed')) {
            $this->newLine();
            $this->showDetailedStatus();
        }

        if ($this->option('stats')) {
            $this->newLine();
            $this->showPerformanceStats($testService);
        }

        return Command::SUCCESS;
    }

    private function showCurrentStatus(): void
    {
        $activeUrl = PlayerUrl::getActive();
        
        if ($activeUrl) {
            $this->line("🟢 <info>URL Activa:</info> {$activeUrl->name}");
            $this->line("   URL: {$activeUrl->url}");
            
            if ($activeUrl->last_tested_at) {
                $this->line("   Último test: {$activeUrl->last_tested_human}");
                
                if ($activeUrl->test_response_time) {
                    $this->line("   Tiempo de respuesta: {$activeUrl->test_response_time} ms");
                }
            } else {
                $this->line("   <comment>Sin probar</comment>");
            }
        } else {
            $this->line("🔴 <error>No hay URLs activas</error>");
        }

        $totalUrls = PlayerUrl::count();
        $inactiveUrls = PlayerUrl::inactive()->count();
        
        $this->newLine();
        $this->line("📈 Resumen:");
        $this->line("   Total de URLs: {$totalUrls}");
        $this->line("   URLs inactivas: {$inactiveUrls}");
    }

    private function showDetailedStatus(): void
    {
        $this->line('<info>📋 Todas las URLs:</info>');
        
        $urls = PlayerUrl::orderBy('is_active', 'desc')
                         ->orderBy('created_at', 'desc')
                         ->get();

        if ($urls->isEmpty()) {
            $this->line('   No hay URLs configuradas');
            return;
        }

        $headers = ['ID', 'Nombre', 'Estado', 'Último Test', 'Resp. Time'];
        $rows = [];

        foreach ($urls as $url) {
            $status = $url->is_active ? '🟢 Activa' : '⚪ Inactiva';
            $lastTest = $url->last_tested_at ? $url->last_tested_at->format('d/m/Y H:i') : 'Sin probar';
            $responseTime = $url->test_response_time ? $url->test_response_time . ' ms' : 'N/A';
            
            $rows[] = [
                $url->id,
                strlen($url->name) > 20 ? substr($url->name, 0, 17) . '...' : $url->name,
                $status,
                $lastTest,
                $responseTime
            ];
        }

        $this->table($headers, $rows);
    }

    private function showPerformanceStats(StreamTestService $testService): void
    {
        $this->line('<info>📊 Estadísticas de Rendimiento:</info>');
        
        $stats = $testService->getPerformanceStats();
        
        $this->line("   Total URLs: {$stats['total_urls']}");
        $this->line("   URLs activas: {$stats['active_urls']}");
        $this->line("   Recientemente probadas: {$stats['recently_tested']}");
        $this->line("   Tests exitosos: {$stats['successful_tests']}");
        
        if ($stats['average_response_time']) {
            $avgTime = round($stats['average_response_time'], 2);
            $this->line("   Tiempo promedio de respuesta: {$avgTime} ms");
        }
        
        if ($stats['fastest_url']) {
            $fastest = $stats['fastest_url'];
            $this->line("   URL más rápida: {$fastest->name} ({$fastest->test_response_time} ms)");
        }
    }
}
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\StreamTestService;
use Illuminate\Console\Command;

class StreamTestAll extends Command
{
    protected $signature = 'stream:test-all 
                          {--inactive : Solo probar URLs inactivas}
                          {--timeout=10 : Tiempo límite en segundos}';

    protected $description = 'Probar conectividad de todas las URLs de streaming';

    public function handle(StreamTestService $testService): int
    {
        $this->info('🔍 Iniciando pruebas de conectividad...');
        
        if ($this->option('inactive')) {
            $results = $testService->testAllInactive();
            $this->info('📊 Probando solo URLs inactivas');
        } else {
            $this->info('📊 Probando todas las URLs');
            $results = [];
            
            \App\Models\PlayerUrl::all()->each(function ($url) use ($testService, &$results) {
                $results[$url->id] = $testService->testPlayerUrl($url);
            });
        }

        $this->displayResults($results);
        
        return Command::SUCCESS;
    }

    private function displayResults(array $results): void
    {
        $successful = collect($results)->where('success', true)->count();
        $total = count($results);
        $failed = $total - $successful;

        $this->newLine();
        $this->info("📈 Resultados:");
        $this->line("✅ Exitosas: {$successful}");
        $this->line("❌ Fallidas: {$failed}");
        $this->line("📊 Total: {$total}");

        if ($failed > 0) {
            $this->newLine();
            $this->warn("⚠️  URLs con problemas:");
            
            foreach ($results as $urlId => $result) {
                if (!$result['success']) {
                    $url = \App\Models\PlayerUrl::find($urlId);
                    if ($url) {
                        $this->line("  • {$url->name}: {$result['message']}");
                    }
                }
            }
        }
    }
}
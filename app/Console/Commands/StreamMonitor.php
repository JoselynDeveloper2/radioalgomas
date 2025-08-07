<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\PlayerUrl;
use App\Services\StreamTestService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class StreamMonitor extends Command
{
    protected $signature = 'stream:monitor 
                          {--interval=15 : Intervalo en minutos entre verificaciones}
                          {--switch : Cambiar automáticamente a URL funcional si la activa falla}';

    protected $description = 'Monitorear continuamente las URLs de streaming';

    public function handle(StreamTestService $testService): int
    {
        $interval = (int) $this->option('interval');
        $autoSwitch = $this->option('switch');

        $this->info("🔍 Iniciando monitoreo de streams");
        $this->line("   Intervalo: {$interval} minutos");
        $this->line("   Auto-switch: " . ($autoSwitch ? 'Activado' : 'Desactivado'));
        $this->newLine();

        while (true) {
            $this->checkActiveUrl($testService, $autoSwitch);
            
            $this->info("⏱️  Esperando {$interval} minutos...");
            sleep($interval * 60);
        }

        return Command::SUCCESS;
    }

    private function checkActiveUrl(StreamTestService $testService, bool $autoSwitch): void
    {
        $activeUrl = PlayerUrl::getActive();
        
        if (!$activeUrl) {
            $this->warn("⚠️  No hay URL activa configurada");
            Log::warning('Stream Monitor: No hay URL activa configurada');
            return;
        }

        $this->info("🔍 Verificando: {$activeUrl->name}");
        
        $result = $testService->testPlayerUrl($activeUrl);
        
        if ($result['success']) {
            $this->info("✅ URL activa funcionando correctamente ({$result['response_time']} ms)");
            Log::info("Stream Monitor: URL activa OK", [
                'url_id' => $activeUrl->id,
                'response_time' => $result['response_time']
            ]);
        } else {
            $this->error("❌ URL activa falló: {$result['message']}");
            Log::error("Stream Monitor: URL activa falló", [
                'url_id' => $activeUrl->id,
                'error' => $result['message']
            ]);
            
            if ($autoSwitch) {
                $this->attemptAutoSwitch($testService, $activeUrl);
            }
        }
    }

    private function attemptAutoSwitch(StreamTestService $testService, PlayerUrl $failedUrl): void
    {
        $this->warn("🔄 Intentando cambio automático...");
        
        $bestUrl = $testService->getBestAvailableUrl();
        
        if (!$bestUrl || $bestUrl->id === $failedUrl->id) {
            // Probar todas las URLs inactivas para encontrar una funcional
            $inactiveUrls = PlayerUrl::inactive()->get();
            
            foreach ($inactiveUrls as $url) {
                $this->line("   Probando: {$url->name}");
                $result = $testService->testPlayerUrl($url);
                
                if ($result['success']) {
                    $bestUrl = $url;
                    break;
                }
            }
        }

        if ($bestUrl && $bestUrl->id !== $failedUrl->id) {
            $this->info("🎯 Cambiando a: {$bestUrl->name}");
            $bestUrl->activate();
            
            Log::info("Stream Monitor: Auto-switch realizado", [
                'from_url_id' => $failedUrl->id,
                'to_url_id' => $bestUrl->id,
                'reason' => 'URL activa falló'
            ]);
            
            $this->info("✅ Cambio automático completado");
        } else {
            $this->error("❌ No se encontró URL alternativa funcional");
            Log::error("Stream Monitor: No hay URLs alternativas disponibles");
        }
    }
}
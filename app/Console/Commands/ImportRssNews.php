<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\RssFeed;
use App\Services\RssImportService;
use Illuminate\Console\Command;

class ImportRssNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rss:import 
                          {--feed= : ID específico del feed a importar}
                          {--limit= : Límite de artículos por feed}
                          {--force : Forzar importación incluso si fue reciente}
                          {--stats : Mostrar estadísticas de importación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importar noticias desde fuentes RSS configuradas';

    /**
     * Execute the console command.
     */
    public function handle(RssImportService $importService): int
    {
        $this->info('🔄 Iniciando importación RSS de noticias');
        $startTime = now();

        try {
            if ($this->option('stats')) {
                $this->displayStats($importService);
                return Command::SUCCESS;
            }

            if ($feedId = $this->option('feed')) {
                return $this->importSpecificFeed((int) $feedId, $importService);
            }

            return $this->importAllFeeds($importService);

        } catch (\Exception $e) {
            $this->error('❌ Error en la importación: ' . $e->getMessage());
            return Command::FAILURE;
        } finally {
            $duration = now()->diffInSeconds($startTime);
            $this->info("⏱️  Tiempo total: {$duration} segundos");
        }
    }

    /**
     * Importar feed específico
     */
    private function importSpecificFeed(int $feedId, RssImportService $importService): int
    {
        $feed = RssFeed::find($feedId);
        
        if (!$feed) {
            $this->error("❌ Feed con ID {$feedId} no encontrado");
            return Command::FAILURE;
        }

        $this->info("📡 Importando feed: {$feed->name}");
        
        if (!$this->option('force') && $feed->isRecentlyFetched()) {
            $this->warn("⚠️  Feed importado recientemente. Use --force para forzar importación");
            return Command::SUCCESS;
        }

        $result = $importService->importFeed($feed);
        $this->displayResult($feed->name, $result);

        return $result['success'] ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Importar todos los feeds
     */
    private function importAllFeeds(RssImportService $importService): int
    {
        $feeds = $this->option('force') 
            ? RssFeed::active()->get() 
            : RssFeed::readyForImport();

        if ($feeds->isEmpty()) {
            $this->info('ℹ️  No hay feeds listos para importar');
            return Command::SUCCESS;
        }

        $this->info("📡 Importando {$feeds->count()} feeds RSS");
        $this->newLine();

        // Crear barra de progreso
        $progressBar = $this->output->createProgressBar($feeds->count());
        $progressBar->start();

        $results = [];
        $totalImported = 0;
        $totalErrors = 0;

        foreach ($feeds as $feed) {
            $result = $importService->importFeed($feed);
            $results[] = $result;
            
            if ($result['success']) {
                $totalImported += $result['imported'];
            } else {
                $totalErrors++;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Mostrar resumen
        $this->displaySummary($results, $totalImported, $totalErrors);

        return $totalErrors === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Mostrar estadísticas
     */
    private function displayStats(RssImportService $importService): void
    {
        $stats = $importService->getImportStats();

        $this->info('📊 Estadísticas de Importación RSS');
        $this->newLine();
        
        $headers = ['Métrica', 'Valor'];
        $rows = [
            ['Total de feeds', $stats['total_feeds']],
            ['Feeds activos', $stats['active_feeds']],
            ['Feeds con errores', $stats['failed_feeds']],
            ['Artículos importados hoy', $stats['imported_articles_today']],
            ['Total artículos importados', $stats['imported_articles_total']],
            ['Última importación', $stats['last_import']?->diffForHumans() ?? 'Nunca'],
        ];

        $this->table($headers, $rows);
    }

    /**
     * Mostrar resultado individual
     */
    private function displayResult(string $feedName, array $result): void
    {
        if ($result['success']) {
            $this->info("✅ {$feedName}:");
            $this->line("   Importados: {$result['imported']}");
            $this->line("   Omitidos: {$result['skipped']}");
            if ($result['errors'] > 0) {
                $this->line("   Errores: {$result['errors']}");
            }
        } else {
            $this->error("❌ {$feedName}: {$result['error']}");
        }
    }

    /**
     * Mostrar resumen completo
     */
    private function displaySummary(array $results, int $totalImported, int $totalErrors): void
    {
        $successfulFeeds = collect($results)->where('success', true)->count();
        $failedFeeds = collect($results)->where('success', false)->count();
        $totalSkipped = collect($results)->sum('skipped');

        $this->info('📈 Resumen de Importación:');
        
        $summaryData = [
            ['Feeds procesados', count($results)],
            ['Feeds exitosos', $successfulFeeds],
            ['Feeds con errores', $failedFeeds],
            ['Artículos importados', $totalImported],
            ['Artículos omitidos', $totalSkipped],
            ['Errores totales', $totalErrors],
        ];

        $this->table(['Concepto', 'Cantidad'], $summaryData);

        if ($totalImported > 0) {
            $this->info("🎉 ¡Importación completada! {$totalImported} artículos nuevos importados");
        }

        if ($totalErrors > 0) {
            $this->warn("⚠️  Se encontraron {$totalErrors} errores durante la importación");
        }

        // Mostrar feeds con errores
        $failedFeeds = collect($results)->filter(fn($r) => !$r['success']);
        if ($failedFeeds->isNotEmpty()) {
            $this->newLine();
            $this->warn('Feeds con errores:');
            
            $feedsWithErrors = RssFeed::failed()->get();
            foreach ($feedsWithErrors as $feed) {
                $this->line("  • {$feed->name}: {$feed->last_error}");
            }
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Jobs\ProcessRssContentJob;
use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessRssWithDelayCommand extends Command
{
    protected $signature = 'rss:process-with-delay 
                            {--delay=30 : Minutes to delay publication (default: 30)}
                            {--batch=10 : Number of articles to process (default: 10)}
                            {--force : Force rewriting even for original content}
                            {--status=draft : Article status to filter by}
                            {--rss-feed= : Process only articles from specific RSS feed ID}
                            {--imported-only : Process only imported articles}
                            {--dry-run : Show what would be processed without executing}';

    protected $description = 'Process RSS articles with configurable delay to avoid duplicate content detection';

    public function handle(): int
    {
        $delay = (int) $this->option('delay');
        $batchSize = (int) $this->option('batch');
        $force = $this->option('force');
        $status = $this->option('status');
        $rssFeedId = $this->option('rss-feed');
        $importedOnly = $this->option('imported-only');
        $dryRun = $this->option('dry-run');

        $this->info("🚀 Starting RSS processing with {$delay} minute delay...");

        // Construir query para los artículos a procesar
        $query = Article::query()->whereNotNull('content');

        if ($status) {
            $query->where('status', $status);
        }

        if ($rssFeedId) {
            $query->where('rss_feed_id', $rssFeedId);
        }

        if ($importedOnly) {
            $query->where('is_imported', true);
        }

        // Filtrar artículos que necesitan procesamiento (a menos que sea forzado)
        if (!$force) {
            $query->where(function($q) {
                $q->whereIn('rewrite_status', ['pending', 'failed'])
                  ->orWhereNull('rewrite_status');
            });
        }

        $articles = $query->orderBy('created_at', 'desc')
                         ->limit($batchSize)
                         ->get();

        if ($articles->isEmpty()) {
            $this->info('✅ No articles found to process with the given criteria.');
            return 0;
        }

        $this->info("📦 Found {$articles->count()} articles to process:");

        $progressBar = $this->output->createProgressBar($articles->count());
        $progressBar->start();

        $queued = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($articles as $index => $article) {
            try {
                $articleDelay = $delay + ($index * 2); // Distribuir los artículos con 2 minutos de diferencia
                
                $this->newLine();
                $this->line("📄 Article: {$article->title}");
                $this->line("   ID: {$article->id}");
                $this->line("   Status: {$article->status}");
                $this->line("   Is Imported: " . ($article->is_imported ? 'Yes' : 'No'));
                $this->line("   Rewrite Status: " . ($article->rewrite_status ?? 'N/A'));
                $this->line("   Scheduled Delay: {$articleDelay} minutes");

                if ($dryRun) {
                    $this->line("   🏃 DRY RUN: Would queue ProcessRssContentJob");
                    $skipped++;
                } else {
                    // Despachar job con delay personalizado
                    ProcessRssContentJob::dispatch(
                        $article, 
                        $force, 
                        $articleDelay
                    );

                    $this->line("   ✅ Queued for processing");
                    $queued++;

                    // Log del despacho
                    Log::info('RSS article queued for processing', [
                        'article_id' => $article->id,
                        'delay_minutes' => $articleDelay,
                        'force_rewrite' => $force,
                        'batch_index' => $index + 1
                    ]);
                }

            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->error("❌ Error processing article {$article->id}: " . $e->getMessage());
                
                Log::error('Error queuing RSS article for processing', [
                    'article_id' => $article->id,
                    'error' => $e->getMessage()
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Resumen
        $this->info('📊 Processing Summary:');
        $this->line("   Total articles found: {$articles->count()}");
        
        if ($dryRun) {
            $this->line("   Would queue: {$skipped}");
        } else {
            $this->line("   Successfully queued: {$queued}");
        }
        
        $this->line("   Errors: {$errors}");
        $this->line("   Base delay: {$delay} minutes");
        $this->line("   Total processing window: " . ($delay + ($articles->count() * 2)) . " minutes");

        if (!$dryRun && $queued > 0) {
            $this->newLine();
            $this->info("🕒 Articles have been queued with staggered delays to avoid duplicate detection.");
            $this->info("📈 Monitor job progress with: php artisan queue:work");
            $this->info("📋 View job status with: php artisan horizon:status (if using Horizon)");
        }

        return $errors > 0 ? 1 : 0;
    }
}

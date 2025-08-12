<?php

namespace App\Jobs;

use App\Models\Article;
use App\Services\ContentRewriterService;
use App\Services\DuplicateDetectorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessRssContentJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $timeout = 300; // 5 minutos timeout
    public $maxExceptions = 3;

    private Article $article;
    private bool $forceRewrite;
    private int $publishDelayMinutes;

    /**
     * Create a new job instance.
     */
    public function __construct(Article $article, bool $forceRewrite = false, int $publishDelayMinutes = 0)
    {
        $this->article = $article;
        $this->forceRewrite = $forceRewrite;
        $this->publishDelayMinutes = $publishDelayMinutes;
        
        // Configurar delay si es necesario
        if ($publishDelayMinutes > 0) {
            $this->delay(now()->addMinutes($publishDelayMinutes));
        }
    }

    /**
     * Execute the job.
     */
    public function handle(
        ContentRewriterService $rewriterService,
        DuplicateDetectorService $duplicateService
    ): void {
        try {
            Log::info('Processing RSS content for article', [
                'article_id' => $this->article->id,
                'title' => $this->article->title,
                'force_rewrite' => $this->forceRewrite,
                'publish_delay' => $this->publishDelayMinutes
            ]);

            // 1. Verificar si el artículo aún existe y necesita procesamiento
            $article = Article::find($this->article->id);
            
            if (!$article) {
                Log::warning('Article not found during RSS processing', [
                    'article_id' => $this->article->id
                ]);
                return;
            }

            // 2. Verificar duplicados
            $duplicateResult = $duplicateService->findDuplicates(
                $article->content, 
                $article->id
            );
            
            $isDuplicate = $duplicateResult['is_duplicate'] || $duplicateResult['has_similar'];
            $contentHash = $duplicateResult['content_hash'];
            
            Log::info('Duplicate detection completed', [
                'article_id' => $article->id,
                'is_duplicate' => $isDuplicate,
                'exact_duplicates' => count($duplicateResult['exact_duplicates']),
                'similar_articles' => count($duplicateResult['similar_articles']),
                'content_hash' => $contentHash
            ]);

            // 3. Actualizar estado de procesamiento
            $article->update([
                'rewrite_status' => 'processing',
                'content_hash' => $contentHash,
            ]);

            // 4. Reescribir contenido si es necesario
            if ($isDuplicate || $this->forceRewrite || $rewriterService->shouldRewrite($article)) {
                
                Log::info('Rewriting article content', [
                    'article_id' => $article->id,
                    'reason' => $isDuplicate ? 'duplicate_detected' : ($this->forceRewrite ? 'forced' : 'should_rewrite')
                ]);

                $rewriteResult = $rewriterService->rewriteArticle($article);
                
                // Actualizar artículo con contenido reescrito
                $article->update([
                    'title' => $rewriteResult['rewritten_title'],
                    'content' => $rewriteResult['rewritten_content'],
                    'rewritten_content' => $rewriteResult['rewritten_content'],
                    'seo_title' => $rewriteResult['seo_data']['seo_title'],
                    'seo_meta_description' => $rewriteResult['seo_data']['seo_meta_description'], 
                    'seo_canonical_url' => $rewriteResult['seo_data']['seo_canonical_url'],
                    'is_original' => false,
                    'rewrite_status' => 'completed',
                    'content_rewritten_at' => now(),
                    'publish_delay' => $this->publishDelayMinutes,
                ]);

                Log::info('Article content rewritten successfully', [
                    'article_id' => $article->id,
                    'word_similarity' => round($rewriteResult['word_similarity'] * 100, 2),
                    'original_words' => str_word_count(strip_tags($rewriteResult['original_content'])),
                    'rewritten_words' => str_word_count(strip_tags($rewriteResult['rewritten_content']))
                ]);

            } else {
                // Solo actualizar hash y estado sin reescribir
                $article->update([
                    'is_original' => !$isDuplicate,
                    'rewrite_status' => 'completed',
                    'publish_delay' => $this->publishDelayMinutes,
                ]);

                Log::info('Article marked as original content', [
                    'article_id' => $article->id,
                    'is_original' => !$isDuplicate
                ]);
            }

            // 5. Configurar publicación programada si hay delay
            if ($this->publishDelayMinutes > 0) {
                $scheduledTime = now()->addMinutes($this->publishDelayMinutes);
                
                $article->update([
                    'scheduled_publish_at' => $scheduledTime,
                    'status' => 'scheduled'
                ]);

                // Programar job para publicar después del delay
                \App\Jobs\ScheduleArticlePublishJob::dispatch($article)
                    ->delay($scheduledTime);

                Log::info('Article scheduled for delayed publication', [
                    'article_id' => $article->id,
                    'scheduled_at' => $scheduledTime->toDateTimeString(),
                    'delay_minutes' => $this->publishDelayMinutes
                ]);
            } else {
                // Publicar inmediatamente si no hay delay y el estado lo permite
                if ($article->status === 'draft') {
                    $article->update([
                        'status' => 'published',
                        'published_at' => now()
                    ]);
                    
                    Log::info('Article published immediately', [
                        'article_id' => $article->id
                    ]);
                }
            }

            // 6. Limpiar metadatos de importación si es necesario
            $this->cleanupImportMetadata($article);

            Log::info('RSS content processing completed successfully', [
                'article_id' => $article->id,
                'final_status' => $article->fresh()->status,
                'is_original' => $article->fresh()->is_original,
                'rewrite_status' => $article->fresh()->rewrite_status
            ]);

        } catch (\Exception $e) {
            $this->handleProcessingError($e);
            throw $e; // Re-throw para que el job sea marcado como fallido
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('RSS content processing job failed', [
            'article_id' => $this->article->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'attempts' => $this->attempts()
        ]);

        // Marcar el artículo como fallido
        try {
            $article = Article::find($this->article->id);
            if ($article) {
                $article->update([
                    'rewrite_status' => 'failed'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update article status after job failure', [
                'article_id' => $this->article->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Manejar errores durante el procesamiento
     */
    private function handleProcessingError(\Exception $e): void
    {
        Log::error('Error processing RSS content', [
            'article_id' => $this->article->id,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'attempts' => $this->attempts()
        ]);

        // Actualizar estado del artículo
        try {
            $article = Article::find($this->article->id);
            if ($article) {
                $article->update([
                    'rewrite_status' => $this->attempts() >= $this->maxExceptions ? 'failed' : 'processing'
                ]);
            }
        } catch (\Exception $updateError) {
            Log::error('Failed to update article status during error handling', [
                'article_id' => $this->article->id,
                'update_error' => $updateError->getMessage()
            ]);
        }
    }

    /**
     * Limpiar metadatos de importación innecesarios
     */
    private function cleanupImportMetadata(Article $article): void
    {
        // Remover metadatos que ya no son necesarios después del procesamiento
        if ($article->import_metadata && is_array($article->import_metadata)) {
            $cleanedMetadata = array_filter($article->import_metadata, function ($key) {
                return !in_array($key, ['temp_content', 'raw_data', 'parsing_errors']);
            }, ARRAY_FILTER_USE_KEY);

            if (count($cleanedMetadata) !== count($article->import_metadata)) {
                $article->update(['import_metadata' => $cleanedMetadata]);
                
                Log::info('Cleaned up import metadata', [
                    'article_id' => $article->id,
                    'removed_keys' => array_diff(array_keys($article->import_metadata), array_keys($cleanedMetadata))
                ]);
            }
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'rss-processing',
            'article:' . $this->article->id,
            'category:' . ($this->article->category->slug ?? 'unknown')
        ];
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [30, 60, 120]; // 30 segundos, 1 minuto, 2 minutos
    }
}

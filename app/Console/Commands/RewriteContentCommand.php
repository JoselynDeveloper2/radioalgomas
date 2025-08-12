<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Services\ContentRewriterService;
use App\Services\DuplicateDetectorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RewriteContentCommand extends Command
{
    protected $signature = 'news:rewrite-content 
                            {--article= : Specific article ID to rewrite}
                            {--batch= : Number of articles to process in batch (default: 10)}
                            {--check-duplicates : Only check for duplicates without rewriting}
                            {--force : Force rewrite even if already processed}
                            {--dry-run : Show what would be done without making changes}
                            {--status= : Filter by rewrite status (pending, completed, failed)}';

    protected $description = 'Rewrite article content to avoid duplicates and improve SEO';

    private ContentRewriterService $rewriterService;
    private DuplicateDetectorService $duplicateService;

    public function __construct(
        ContentRewriterService $rewriterService,
        DuplicateDetectorService $duplicateService
    ) {
        parent::__construct();
        $this->rewriterService = $rewriterService;
        $this->duplicateService = $duplicateService;
    }

    public function handle(): int
    {
        $this->info('🚀 Starting content rewriting process...');
        
        // Mostrar estadísticas iniciales
        $this->showStatistics();
        
        if ($this->option('check-duplicates')) {
            return $this->checkDuplicatesOnly();
        }
        
        if ($articleId = $this->option('article')) {
            return $this->processSpecificArticle((int) $articleId);
        }
        
        return $this->processBatch();
    }

    private function processSpecificArticle(int $articleId): int
    {
        $article = Article::find($articleId);
        
        if (!$article) {
            $this->error("❌ Article with ID {$articleId} not found.");
            return 1;
        }
        
        $this->info("📄 Processing article: {$article->title}");
        
        return $this->processArticle($article) ? 0 : 1;
    }

    private function processBatch(): int
    {
        $batchSize = (int) $this->option('batch', 10);
        $status = $this->option('status');
        $force = $this->option('force');
        
        $query = Article::query()->whereNotNull('content');
        
        if ($status) {
            $query->where('rewrite_status', $status);
        } elseif (!$force) {
            $query->whereIn('rewrite_status', ['pending', 'failed'])
                  ->orWhereNull('rewrite_status');
        }
        
        $articles = $query->orderBy('created_at', 'desc')
                         ->limit($batchSize)
                         ->get();
        
        if ($articles->isEmpty()) {
            $this->info('✅ No articles found to process.');
            return 0;
        }
        
        $this->info("📦 Processing batch of {$articles->count()} articles...");
        
        $progressBar = $this->output->createProgressBar($articles->count());
        $progressBar->start();
        
        $processed = 0;
        $errors = 0;
        
        foreach ($articles as $article) {
            try {
                if ($this->processArticle($article)) {
                    $processed++;
                }
            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->error("❌ Error processing article {$article->id}: " . $e->getMessage());
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        $this->info("✅ Batch completed:");
        $this->line("   - Processed: {$processed}");
        $this->line("   - Errors: {$errors}");
        
        return $errors > 0 ? 1 : 0;
    }

    private function processArticle(Article $article): bool
    {
        try {
            // 1. Verificar duplicados
            $this->line("🔍 Checking for duplicates...");
            $duplicateResult = $this->duplicateService->findDuplicates(
                $article->content, 
                $article->id
            );
            
            $isDuplicate = $duplicateResult['is_duplicate'] || $duplicateResult['has_similar'];
            
            if ($isDuplicate) {
                $this->warn("   ⚠️  Duplicate/similar content detected:");
                $this->line("      - Exact duplicates: " . count($duplicateResult['exact_duplicates']));
                $this->line("      - Similar articles: " . count($duplicateResult['similar_articles']));
            } else {
                $this->line("   ✅ No duplicates found");
            }
            
            // Marcar estado de duplicado
            $contentHash = $duplicateResult['content_hash'];
            
            if ($this->option('dry-run')) {
                $this->info("   🏃 DRY RUN: Would update content hash and duplicate status");
                return true;
            }
            
            // 2. Reescribir contenido si es necesario
            if ($isDuplicate || $this->rewriterService->shouldRewrite($article)) {
                $this->line("✍️  Rewriting content...");
                
                $article->update(['rewrite_status' => 'processing']);
                
                $rewriteResult = $this->rewriterService->rewriteArticle($article);
                
                // Actualizar artículo con contenido reescrito
                $article->update([
                    'title' => $rewriteResult['rewritten_title'],
                    'content' => $rewriteResult['rewritten_content'],
                    'rewritten_content' => $rewriteResult['rewritten_content'],
                    'seo_title' => $rewriteResult['seo_data']['seo_title'],
                    'seo_meta_description' => $rewriteResult['seo_data']['seo_meta_description'],
                    'seo_canonical_url' => $rewriteResult['seo_data']['seo_canonical_url'],
                    'content_hash' => $contentHash,
                    'is_original' => false,
                    'rewrite_status' => 'completed',
                    'content_rewritten_at' => now(),
                ]);
                
                $this->line("   ✅ Content rewritten successfully");
                $this->line("   📊 Word similarity: " . round($rewriteResult['word_similarity'] * 100, 2) . "%");
            } else {
                // Solo actualizar hash y estado
                $article->update([
                    'content_hash' => $contentHash,
                    'is_original' => !$isDuplicate,
                    'rewrite_status' => 'completed',
                ]);
                
                $this->line("   ℹ️  Article marked as original content");
            }
            
            return true;
            
        } catch (\Exception $e) {
            $article->update(['rewrite_status' => 'failed']);
            
            Log::error('Error processing article in rewrite command', [
                'article_id' => $article->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function checkDuplicatesOnly(): int
    {
        $this->info("🔍 Checking for duplicates only...");
        
        $articles = Article::whereNotNull('content')
                          ->whereNull('content_hash')
                          ->limit(50)
                          ->get();
        
        if ($articles->isEmpty()) {
            $this->info('✅ No articles to check.');
            return 0;
        }
        
        $duplicates = [];
        $progressBar = $this->output->createProgressBar($articles->count());
        $progressBar->start();
        
        foreach ($articles as $article) {
            $result = $this->duplicateService->findDuplicates($article->content, $article->id);
            
            if ($result['is_duplicate'] || $result['has_similar']) {
                $duplicates[] = [
                    'id' => $article->id,
                    'title' => $article->title,
                    'exact_duplicates' => count($result['exact_duplicates']),
                    'similar_articles' => count($result['similar_articles'])
                ];
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        if (empty($duplicates)) {
            $this->info('✅ No duplicates found in the checked articles.');
        } else {
            $this->warn("⚠️  Found {" . count($duplicates) . "} articles with duplicate/similar content:");
            
            foreach ($duplicates as $duplicate) {
                $this->line("   📄 ID: {$duplicate['id']} - {$duplicate['title']}");
                $this->line("      Exact: {$duplicate['exact_duplicates']}, Similar: {$duplicate['similar_articles']}");
            }
        }
        
        return 0;
    }

    private function showStatistics(): void
    {
        $stats = $this->duplicateService->getStatistics();
        
        $this->info('📊 Content Statistics:');
        $this->line("   Total articles: {$stats['total_articles']}");
        $this->line("   Articles with hash: {$stats['articles_with_hash']}");
        $this->line("   Original content: {$stats['original_articles']}");
        $this->line("   Duplicate content: {$stats['duplicate_articles']}");
        $this->line("   Pending rewrite: {$stats['pending_rewrite']}");
        $this->line("   Completed rewrite: {$stats['completed_rewrite']}");
        $this->line("   Failed rewrite: {$stats['failed_rewrite']}");
        $this->line("   Unique content hashes: {$stats['unique_hashes']}");
        $this->newLine();
    }
}

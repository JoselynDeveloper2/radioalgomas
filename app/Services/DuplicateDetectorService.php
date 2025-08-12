<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DuplicateDetectorService
{
    private int $minSimilarityThreshold = 80; // 80% de similitud
    private int $hashLength = 64;

    public function generateContentHash(string $content): string
    {
        // Normalizar el contenido para generar hash consistente
        $normalizedContent = $this->normalizeContent($content);
        
        // Generar hash SHA-256 del contenido normalizado
        return hash('sha256', $normalizedContent);
    }

    public function findDuplicates(string $content, ?int $excludeArticleId = null): array
    {
        $contentHash = $this->generateContentHash($content);
        
        // Buscar hash exacto
        $exactDuplicates = $this->findExactDuplicates($contentHash, $excludeArticleId);
        
        // Buscar similares usando algoritmo de similitud textual
        $similarArticles = $this->findSimilarContent($content, $excludeArticleId);
        
        return [
            'exact_duplicates' => $exactDuplicates,
            'similar_articles' => $similarArticles,
            'content_hash' => $contentHash,
            'is_duplicate' => !empty($exactDuplicates),
            'has_similar' => !empty($similarArticles),
            'total_matches' => count($exactDuplicates) + count($similarArticles)
        ];
    }

    public function isDuplicate(string $content, ?int $excludeArticleId = null): bool
    {
        $result = $this->findDuplicates($content, $excludeArticleId);
        return $result['is_duplicate'] || $result['has_similar'];
    }

    public function markAsProcessed(Article $article, string $contentHash, bool $isDuplicate = false): void
    {
        $article->update([
            'content_hash' => $contentHash,
            'is_original' => !$isDuplicate,
            'rewrite_status' => $isDuplicate ? 'pending' : 'completed'
        ]);

        Log::info('Article duplicate status updated', [
            'article_id' => $article->id,
            'content_hash' => $contentHash,
            'is_duplicate' => $isDuplicate,
            'is_original' => !$isDuplicate
        ]);
    }

    public function analyzeBatchDuplicates(array $articles): array
    {
        $results = [];
        $processedHashes = [];

        foreach ($articles as $article) {
            if (!$article instanceof Article) {
                continue;
            }

            $content = $article->content ?? '';
            $hash = $this->generateContentHash($content);
            
            // Verificar si ya procesamos este hash en el lote
            if (isset($processedHashes[$hash])) {
                $results[] = [
                    'article_id' => $article->id,
                    'content_hash' => $hash,
                    'is_duplicate' => true,
                    'duplicate_of' => $processedHashes[$hash],
                    'similarity_score' => 100
                ];
            } else {
                // Buscar duplicados en la base de datos
                $duplicates = $this->findDuplicates($content, $article->id);
                
                $results[] = [
                    'article_id' => $article->id,
                    'content_hash' => $hash,
                    'is_duplicate' => $duplicates['is_duplicate'] || $duplicates['has_similar'],
                    'exact_matches' => count($duplicates['exact_duplicates']),
                    'similar_matches' => count($duplicates['similar_articles']),
                    'duplicates' => $duplicates
                ];
                
                $processedHashes[$hash] = $article->id;
            }
        }

        return [
            'processed_articles' => count($articles),
            'duplicate_articles' => count(array_filter($results, fn($r) => $r['is_duplicate'])),
            'unique_articles' => count(array_filter($results, fn($r) => !$r['is_duplicate'])),
            'results' => $results
        ];
    }

    private function findExactDuplicates(string $contentHash, ?int $excludeArticleId = null): array
    {
        $query = Article::where('content_hash', $contentHash);
        
        if ($excludeArticleId) {
            $query->where('id', '!=', $excludeArticleId);
        }
        
        return $query->get()->map(function($article) {
            return [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'created_at' => $article->created_at,
                'similarity_score' => 100
            ];
        })->toArray();
    }

    private function findSimilarContent(string $content, ?int $excludeArticleId = null): array
    {
        $normalizedContent = $this->normalizeContent($content);
        $contentWords = $this->extractKeywords($normalizedContent);
        
        if (empty($contentWords)) {
            return [];
        }

        // Buscar artículos con palabras clave similares
        $query = Article::whereNotNull('content')
            ->where('content', '!=', '');
            
        if ($excludeArticleId) {
            $query->where('id', '!=', $excludeArticleId);
        }

        $candidates = $query->limit(100)->get();
        $similarArticles = [];

        foreach ($candidates as $candidate) {
            $similarity = $this->calculateTextSimilarity($content, $candidate->content);
            
            if ($similarity >= $this->minSimilarityThreshold) {
                $similarArticles[] = [
                    'id' => $candidate->id,
                    'title' => $candidate->title,
                    'slug' => $candidate->slug,
                    'created_at' => $candidate->created_at,
                    'similarity_score' => round($similarity, 2)
                ];
            }
        }

        // Ordenar por similitud descendente
        usort($similarArticles, fn($a, $b) => $b['similarity_score'] <=> $a['similarity_score']);

        return $similarArticles;
    }

    private function normalizeContent(string $content): string
    {
        // Remover HTML
        $text = strip_tags($content);
        
        // Convertir a minúsculas
        $text = mb_strtolower($text, 'UTF-8');
        
        // Remover caracteres especiales y normalizar espacios
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Remover palabras comunes (stop words)
        $stopWords = [
            'el', 'la', 'de', 'que', 'y', 'a', 'en', 'un', 'es', 'se', 'no', 'te',
            'lo', 'le', 'da', 'su', 'por', 'son', 'con', 'para', 'al', 'del',
            'los', 'las', 'una', 'como', 'pero', 'sus', 'ya', 'o', 'fue', 'este',
            'ha', 'si', 'porque', 'esta', 'entre', 'cuando', 'muy', 'sin', 'sobre',
            'ser', 'tiene', 'también', 'me', 'hasta', 'hay', 'donde', 'han', 'quien',
            'están', 'estado', 'desde', 'todo', 'nos', 'durante', 'todos', 'uno',
            'les', 'ni', 'contra', 'otros', 'fueron', 'ese', 'eso', 'había', 'ante',
            'ellos', 'e', 'esto', 'mí', 'antes', 'algunos', 'qué', 'unos', 'yo',
            'otro', 'otras', 'otra', 'él', 'tanto', 'esa', 'estos', 'mucho',
            'quienes', 'nada', 'muchos', 'cual', 'poco', 'ella', 'estar', 'haber',
            'estas', 'estaba', 'estamos', 'pueden', 'hacen', 'entonces', 'tiempo',
            'cada', 'más', 'años', 'año', 'día', 'días'
        ];
        
        $words = explode(' ', $text);
        $filteredWords = array_filter($words, function($word) use ($stopWords) {
            return !empty($word) && !in_array(trim($word), $stopWords) && strlen(trim($word)) > 2;
        });
        
        return implode(' ', $filteredWords);
    }

    private function extractKeywords(string $normalizedContent): array
    {
        $words = explode(' ', $normalizedContent);
        $wordCount = array_count_values($words);
        
        // Filtrar palabras que aparecen al menos 2 veces
        $keywords = array_filter($wordCount, fn($count) => $count >= 2);
        arsort($keywords);
        
        return array_slice(array_keys($keywords), 0, 20);
    }

    private function calculateTextSimilarity(string $text1, string $text2): float
    {
        $normalized1 = $this->normalizeContent($text1);
        $normalized2 = $this->normalizeContent($text2);
        
        $words1 = array_unique(explode(' ', $normalized1));
        $words2 = array_unique(explode(' ', $normalized2));
        
        if (empty($words1) || empty($words2)) {
            return 0;
        }
        
        // Similitud Jaccard
        $intersection = array_intersect($words1, $words2);
        $union = array_unique(array_merge($words1, $words2));
        
        $jaccardSimilarity = count($intersection) / count($union);
        
        // Ajustar por longitud del contenido
        $lengthRatio = min(strlen($normalized1), strlen($normalized2)) / max(strlen($normalized1), strlen($normalized2));
        
        return ($jaccardSimilarity * 0.7 + $lengthRatio * 0.3) * 100;
    }

    public function getStatistics(): array
    {
        return [
            'total_articles' => Article::count(),
            'articles_with_hash' => Article::whereNotNull('content_hash')->count(),
            'duplicate_articles' => Article::where('is_original', false)->count(),
            'original_articles' => Article::where('is_original', true)->count(),
            'pending_rewrite' => Article::where('rewrite_status', 'pending')->count(),
            'completed_rewrite' => Article::where('rewrite_status', 'completed')->count(),
            'failed_rewrite' => Article::where('rewrite_status', 'failed')->count(),
            'unique_hashes' => Article::whereNotNull('content_hash')->distinct('content_hash')->count(),
        ];
    }

    public function cleanupDuplicates(bool $dryRun = true): array
    {
        $duplicateGroups = DB::table('articles')
            ->select('content_hash', DB::raw('COUNT(*) as count'), DB::raw('MIN(id) as oldest_id'))
            ->whereNotNull('content_hash')
            ->groupBy('content_hash')
            ->having('count', '>', 1)
            ->get();

        $results = [
            'duplicate_groups' => $duplicateGroups->count(),
            'total_duplicates' => 0,
            'would_delete' => 0,
            'deleted' => 0,
            'groups' => []
        ];

        foreach ($duplicateGroups as $group) {
            $duplicates = Article::where('content_hash', $group->content_hash)
                ->where('id', '!=', $group->oldest_id)
                ->get();

            $results['total_duplicates'] += $duplicates->count();
            $results['would_delete'] += $duplicates->count();
            
            $results['groups'][] = [
                'content_hash' => $group->content_hash,
                'total_count' => $group->count,
                'oldest_id' => $group->oldest_id,
                'duplicates' => $duplicates->pluck('id')->toArray()
            ];

            if (!$dryRun) {
                $deleted = $duplicates->count();
                Article::whereIn('id', $duplicates->pluck('id'))->delete();
                $results['deleted'] += $deleted;
            }
        }

        return $results;
    }
}
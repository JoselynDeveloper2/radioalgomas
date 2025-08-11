<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Article;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Google\Client as GoogleClient;
use Google\Service\Indexing as GoogleIndexingService;
use Google\Service\Indexing\UrlNotification;

class ArticleObserver
{
    /**
     * Handle the Article "creating" event.
     */
    public function creating(Article $article): void
    {
        $this->generateSlug($article);
        $this->generateSeoFields($article);
        $this->calculateReadingTime($article);
    }

    /**
     * Handle the Article "updating" event.
     */
    public function updating(Article $article): void
    {
        // Solo actualizar slug si está vacío o si el título cambió
        if ($article->isDirty('title') && (empty($article->slug) || $article->slug === Str::slug($article->getOriginal('title')))) {
            $this->generateSlug($article);
        }

        // Actualizar campos SEO si el título o contenido cambió
        if ($article->isDirty(['title', 'content', 'excerpt'])) {
            $this->generateSeoFields($article);
        }

        // Recalcular tiempo de lectura si el contenido cambió
        if ($article->isDirty('content')) {
            $this->calculateReadingTime($article);
        }
    }

    /**
     * Handle the Article "saved" event.
     */
    public function saved(Article $article): void
    {
        // Generar schema markup después de guardar (para tener ID)
        if (empty($article->schema_markup) || $article->wasRecentlyCreated) {
            $article->updateQuietly([
                'schema_markup' => $article->generateSchemaMarkup()
            ]);
        }

        // Notificar a Google cuando el artículo se publica o actualiza
        if ($article->isPublished()) {
            $this->notifyGoogleIndexing($article);
        }
    }

    /**
     * Handle the Article "deleted" event.
     */
    public function deleted(Article $article): void
    {
        // Notificar a Google sobre la eliminación del artículo
        if ($article->isPublished()) {
            $this->notifyGoogleUrlDeleted($article);
        }
    }

    /**
     * Handle the Article "restored" event.
     */
    public function restored(Article $article): void
    {
        // Regenerar campos SEO al restaurar
        $this->generateSeoFields($article);
    }

    /**
     * Handle the Article "force deleted" event.
     */
    public function forceDeleted(Article $article): void
    {
        // Lógica adicional al eliminar permanentemente si es necesario
    }

    /**
     * Generar slug único para el artículo
     */
    private function generateSlug(Article $article): void
    {
        if (empty($article->slug) && !empty($article->title)) {
            $baseSlug = Str::slug($article->title);
            $slug = $baseSlug;
            $counter = 1;

            // Verificar que el slug sea único
            while (Article::where('slug', $slug)
                         ->where('id', '!=', $article->id ?? 0)
                         ->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $article->slug = $slug;
        }
    }

    /**
     * Generar campos SEO automáticamente
     */
    private function generateSeoFields(Article $article): void
    {
        // Meta title
        if (empty($article->meta_title)) {
            $article->meta_title = Str::limit($article->title, 60);
        }

        // Meta description
        if (empty($article->meta_description)) {
            if (!empty($article->excerpt)) {
                $article->meta_description = Str::limit($article->excerpt, 160);
            } else {
                $article->meta_description = Str::limit(strip_tags($article->content), 160);
            }
        }

        // Meta keywords (extraer palabras clave del título y contenido)
        if (empty($article->meta_keywords)) {
            $article->meta_keywords = $this->generateKeywords($article);
        }

        // Open Graph fields
        if (empty($article->og_title)) {
            $article->og_title = $article->meta_title;
        }

        if (empty($article->og_description)) {
            $article->og_description = $article->meta_description;
        }

        // Canonical URL
        if (empty($article->canonical_url)) {
            $article->canonical_url = url('/articulos/' . ($article->slug ?: Str::slug($article->title)));
        }
    }

    /**
     * Calcular tiempo de lectura
     */
    private function calculateReadingTime(Article $article): void
    {
        if (!empty($article->content)) {
            $wordCount = str_word_count(strip_tags($article->content));
            $article->reading_time = max(1, ceil($wordCount / 200)); // 200 palabras por minuto
        }
    }

    /**
     * Generar palabras clave automáticamente
     */
    private function generateKeywords(Article $article): string
    {
        try {
            $text = $article->title . ' ' . strip_tags($article->content);
            
            // Limpiar y normalizar texto para UTF-8
            $text = mb_strtolower($text, 'UTF-8');
            $text = $this->cleanUtf8Text($text);
            
            // Remover palabras comunes en español
            $stopWords = [
                'el', 'la', 'de', 'que', 'y', 'a', 'en', 'un', 'es', 'se', 'no', 'te', 'lo', 'le',
                'da', 'su', 'por', 'son', 'con', 'para', 'al', 'del', 'los', 'las', 'una', 'como',
                'pero', 'sus', 'le', 'ya', 'o', 'fue', 'este', 'ha', 'si', 'porque', 'esta', 'son',
                'entre', 'cuando', 'muy', 'sin', 'sobre', 'ser', 'tiene', 'también', 'me', 'hasta',
                'hay', 'donde', 'han', 'quien', 'están', 'estado', 'desde', 'todo', 'nos', 'durante',
                'todos', 'uno', 'les', 'ni', 'contra', 'otros', 'fueron', 'ese', 'eso', 'había',
                'ante', 'ellos', 'e', 'esto', 'mí', 'antes', 'algunos', 'qué', 'unos', 'yo', 'otro',
                'otras', 'otra', 'él', 'tanto', 'esa', 'estos', 'mucho', 'quienes', 'nada', 'muchos',
                'cual', 'poco', 'ella', 'estar', 'haber', 'estas', 'estaba', 'estamos', 'pueden',
                'hacen', 'entonces', 'tiempo', 'cada', 'más', 'años', 'año', 'día', 'días'
            ];
            
            // Extraer palabras usando expresión regular para UTF-8
            preg_match_all('/\b\p{L}+\b/u', $text, $matches);
            $words = $matches[0];
            
            $words = array_filter($words, function($word) use ($stopWords) {
                return mb_strlen($word, 'UTF-8') > 3 && !in_array($word, $stopWords);
            });
            
            // Contar frecuencia
            $wordCount = array_count_values($words);
            arsort($wordCount);
            
            // Tomar las 10 palabras más frecuentes y limpiarlas
            $keywords = array_slice(array_keys($wordCount), 0, 10);
            $keywords = array_map([$this, 'cleanUtf8Text'], $keywords);
            
            return implode(', ', $keywords);
            
        } catch (\Exception $e) {
            Log::warning('Error generando keywords para artículo', [
                'article_id' => $article->id ?? 'nuevo',
                'error' => $e->getMessage()
            ]);
            return '';
        }
    }

    /**
     * Limpiar texto UTF-8 problemático
     */
    private function cleanUtf8Text(string $text): string
    {
        // Convertir a UTF-8 válido
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        
        // Remover caracteres de control y no válidos
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        
        // Remover emojis y caracteres especiales problemáticos
        $text = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $text); // emoticons
        $text = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '', $text); // misc symbols
        $text = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $text); // transport
        $text = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $text); // misc symbols
        $text = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $text); // dingbats
        
        return trim($text);
    }

    /**
     * Notificar a Google sobre la indexación del artículo
     */
    private function notifyGoogleIndexing(Article $article): void
    {
        try {
            $client = $this->getGoogleClient();
            if (!$client) {
                Log::info('Google Indexing service account credentials not configured, skipping notification for article: ' . $article->id);
                return;
            }

            $url = $article->canonical_url;
            $indexingService = new GoogleIndexingService($client);
            
            $urlNotification = new UrlNotification();
            $urlNotification->setUrl($url);
            $urlNotification->setType('URL_UPDATED');

            $response = $indexingService->urlNotifications->publish($urlNotification);

            Log::info("Google indexing notification sent successfully for article: {$article->id} - {$url}", [
                'notification_time' => $response->getNotifyTime(),
                'url' => $url
            ]);

        } catch (\Google\Service\Exception $e) {
            Log::warning("Google API error sending indexing notification for article: {$article->id}", [
                'error' => $e->getMessage(),
                'errors' => $e->getErrors(),
                'url' => $article->canonical_url ?? 'N/A'
            ]);
        } catch (\Exception $e) {
            Log::error("Error sending Google indexing notification for article: {$article->id}", [
                'error' => $e->getMessage(),
                'url' => $article->canonical_url ?? 'N/A'
            ]);
        }
    }

    /**
     * Notificar a Google sobre la eliminación de un artículo
     */
    private function notifyGoogleUrlDeleted(Article $article): void
    {
        try {
            $client = $this->getGoogleClient();
            if (!$client) {
                Log::info('Google Indexing service account credentials not configured, skipping deletion notification for article: ' . $article->id);
                return;
            }

            $url = $article->canonical_url;
            $indexingService = new GoogleIndexingService($client);
            
            $urlNotification = new UrlNotification();
            $urlNotification->setUrl($url);
            $urlNotification->setType('URL_DELETED');

            $response = $indexingService->urlNotifications->publish($urlNotification);

            Log::info("Google URL deletion notification sent successfully for article: {$article->id} - {$url}", [
                'notification_time' => $response->getNotifyTime(),
                'url' => $url
            ]);

        } catch (\Google\Service\Exception $e) {
            Log::warning("Google API error sending URL deletion notification for article: {$article->id}", [
                'error' => $e->getMessage(),
                'errors' => $e->getErrors(),
                'url' => $article->canonical_url ?? 'N/A'
            ]);
        } catch (\Exception $e) {
            Log::error("Error sending Google URL deletion notification for article: {$article->id}", [
                'error' => $e->getMessage(),
                'url' => $article->canonical_url ?? 'N/A'
            ]);
        }
    }

    /**
     * Obtener cliente de Google configurado para la API de indexing
     */
    private function getGoogleClient(): ?GoogleClient
    {
        try {
            $serviceAccountJson = config('services.google.indexing.service_account_json');
            $serviceAccountPath = config('services.google.indexing.service_account_path');
            $scopes = config('services.google.indexing.scopes', ['https://www.googleapis.com/auth/indexing']);

            if (!$serviceAccountJson && !$serviceAccountPath) {
                Log::debug('Google Indexing service account credentials not configured');
                return null;
            }

            $client = new GoogleClient();
            $client->setScopes($scopes);

            if ($serviceAccountJson) {
                $credentials = json_decode($serviceAccountJson, true);
                if (!$credentials) {
                    Log::error('Invalid JSON format in GOOGLE_INDEXING_SERVICE_ACCOUNT_JSON');
                    return null;
                }
                $client->setAuthConfig($credentials);
                Log::debug('Google Client configured with service account JSON string');
            } elseif ($serviceAccountPath) {
                // Si la ruta es absoluta, usarla tal como está
                if (str_starts_with($serviceAccountPath, '/') || str_contains($serviceAccountPath, ':\\')) {
                    $fullPath = $serviceAccountPath;
                } else {
                    // Si es relativa y empieza con 'storage/', usar base_path()
                    if (str_starts_with($serviceAccountPath, 'storage/')) {
                        $fullPath = base_path($serviceAccountPath);
                    } else {
                        // Si es solo el archivo, asumir que está en storage/app/
                        $fullPath = storage_path('app/' . ltrim($serviceAccountPath, '/'));
                    }
                }
                
                if (file_exists($fullPath)) {
                    $client->setAuthConfig($fullPath);
                    Log::debug('Google Client configured with service account file path: ' . $fullPath);
                } else {
                    Log::error('Google service account file not found at path: ' . $fullPath);
                    return null;
                }
            } else {
                Log::error('Google service account path not configured');
                return null;
            }

            $client->useApplicationDefaultCredentials();
            return $client;

        } catch (\InvalidArgumentException $e) {
            Log::error('Invalid Google service account configuration: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error('Error configuring Google client: ' . $e->getMessage());
            return null;
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Article;
use App\Models\RssFeed;
use App\Models\Tag;
use Feeds;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Carbon\Carbon;
use Exception;

class RssImportService
{
    private int $maxItemsPerFeed = 10;
    private int $imageTimeout = 30;
    private array $allowedImageTypes = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function __construct()
    {
        // Configurar timeout para Feeds
        ini_set('user_agent', 'TuCanalTV RSS Bot/1.0 (compatible)');
    }

    /**
     * Importar todas las fuentes RSS activas
     */
    public function importAll(): array
    {
        $feeds = RssFeed::readyForImport();
        $results = [];

        Log::info('Iniciando importación RSS masiva', [
            'total_feeds' => $feeds->count()
        ]);

        foreach ($feeds as $feed) {
            $results[$feed->id] = $this->importFeed($feed);
            
            // Pequeña pausa entre feeds para evitar sobrecarga
            usleep(500000); // 0.5 segundos
        }

        $this->generateImportSummary($results);
        
        return $results;
    }

    /**
     * Importar una fuente RSS específica
     */
    public function importFeed(RssFeed $feed): array
    {
        Log::info('Iniciando importación de feed RSS', [
            'feed_id' => $feed->id,
            'feed_name' => $feed->name,
            'feed_url' => $feed->url
        ]);

        $feed->markAsTesting();
        
        try {
            $rssContent = $this->fetchRssFeed($feed->url);
            if (!$rssContent) {
                throw new Exception('No se pudo obtener el contenido RSS');
            }

            $items = $this->parseRssItems($rssContent);
            if (empty($items)) {
                throw new Exception('No se encontraron elementos en el RSS');
            }

            $importedCount = 0;
            $skippedCount = 0;
            $errorCount = 0;

            foreach (array_slice($items, 0, $this->maxItemsPerFeed) as $item) {
                try {
                    $result = $this->processRssItem($item, $feed);
                    
                    if ($result === 'imported') {
                        $importedCount++;
                    } elseif ($result === 'skipped') {
                        $skippedCount++;
                    }
                } catch (Exception $e) {
                    $errorCount++;
                    Log::warning('Error procesando item RSS', [
                        'feed_id' => $feed->id,
                        'item_title' => $item['title'] ?? 'Sin título',
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $feed->incrementSuccess();

            $result = [
                'success' => true,
                'imported' => $importedCount,
                'skipped' => $skippedCount,
                'errors' => $errorCount,
                'total_processed' => $importedCount + $skippedCount + $errorCount,
            ];

            Log::info('Importación RSS completada exitosamente', array_merge(
                ['feed_id' => $feed->id, 'feed_name' => $feed->name],
                $result
            ));

            return $result;

        } catch (Exception $e) {
            $feed->markAsFailed($e->getMessage());
            
            $result = [
                'success' => false,
                'error' => $e->getMessage(),
                'imported' => 0,
                'skipped' => 0,
                'errors' => 1,
            ];

            Log::error('Error en importación RSS', [
                'feed_id' => $feed->id,
                'feed_name' => $feed->name,
                'error' => $e->getMessage()
            ]);

            return $result;
        }
    }

    /**
     * Obtener contenido RSS de una URL
     */
    private function fetchRssFeed(string $url): ?array
    {
        try {
            $feed = Feeds::make($url, 300); // Cache por 5 minutos
            
            if (!$feed) {
                Log::warning('No se pudo crear el feed RSS', ['url' => $url]);
                return null;
            }

            $items = [];
            foreach ($feed->get_items() as $item) {
                $items[] = [
                    'title' => $item->get_title(),
                    'description' => $item->get_description(),
                    'content' => $item->get_content(),
                    'link' => $item->get_permalink(),
                    'published_at' => $item->get_date('Y-m-d H:i:s'),
                    'id' => $item->get_id(),
                    'enclosures' => $this->extractEnclosures($item),
                ];
            }

            return $items;

        } catch (Exception $e) {
            Log::error('Error obteniendo RSS feed', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Extraer archivos multimedia (imágenes) de un item RSS
     */
    private function extractEnclosures($item): array
    {
        $enclosures = [];
        
        if ($item->get_enclosures()) {
            foreach ($item->get_enclosures() as $enclosure) {
                if ($enclosure->get_medium() === 'image' || 
                    str_starts_with($enclosure->get_type() ?? '', 'image/')) {
                    $enclosures[] = [
                        'url' => $enclosure->get_link(),
                        'type' => $enclosure->get_type(),
                    ];
                }
            }
        }

        // Buscar imágenes en el contenido HTML
        $content = $item->get_description() . ' ' . $item->get_content();
        if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches)) {
            foreach ($matches[1] as $imageUrl) {
                $enclosures[] = [
                    'url' => $imageUrl,
                    'type' => 'image/unknown',
                ];
            }
        }

        return $enclosures;
    }

    /**
     * Parsear elementos del RSS
     */
    private function parseRssItems(array $items): array
    {
        $parsed = [];
        
        foreach ($items as $item) {
            // Validaciones básicas
            if (empty($item['title']) || empty($item['link'])) {
                continue;
            }

            // Limpiar contenido
            $item['title'] = html_entity_decode(strip_tags($item['title']));
            $item['description'] = $this->cleanHtmlContent($item['description'] ?? '');
            $item['content'] = $this->cleanHtmlContent($item['content'] ?? $item['description'] ?? '');
            
            // Generar external_id único (normalizando título)
            $normalizedTitle = Str::slug($item['title']);
            $item['external_id'] = md5($item['link'] . $normalizedTitle);
            
            $parsed[] = $item;
        }

        return $parsed;
    }

    /**
     * Procesar un elemento RSS individual
     */
    private function processRssItem(array $item, RssFeed $feed): string
    {
        // Verificar si ya existe por external_id
        $existingArticle = Article::where('rss_feed_id', $feed->id)
            ->where('external_id', $item['external_id'])
            ->first();

        // Verificar también por URL directa para evitar duplicados si cambia el título
        if (!$existingArticle) {
            $existingArticle = Article::where('source_url', $item['link'])->first();
        }

        if ($existingArticle) {
            Log::info('Artículo omitido: ya existe', [
                'title' => $item['title'],
                'external_id' => $item['external_id'],
                'feed_id' => $feed->id,
                'existing_id' => $existingArticle->id
            ]);
            return 'skipped';
        }

        // Procesar imagen destacada
        $featuredImage = $this->processFeaturedImage($item['enclosures'] ?? []);
        
        // Solo guardar si tiene imagen (según requerimiento)
        if (!$featuredImage) {
            Log::info('Artículo descartado: sin imagen válida', [
                'title' => $item['title'],
                'feed_id' => $feed->id,
                'enclosures_count' => count($item['enclosures'] ?? []),
                'enclosures' => $item['enclosures'] ?? []
            ]);
            return 'skipped';
        }

        // Crear el artículo con manejo de duplicados
        try {
            $article = Article::create([
                'title' => Str::limit($item['title'], 255),
                'slug' => $this->generateUniqueSlug($item['title']),
                'excerpt' => $this->generateExcerpt($item['description']),
                'content' => $this->addSourceCredit($item['content'], $item['link'], $feed->name),
                'featured_image' => $featuredImage,
                'source_url' => $item['link'],
                'canonical_url' => $item['link'], // Canonical pointing to original source
                'external_id' => $item['external_id'],
                'rss_feed_id' => $feed->id,
                'category_id' => $feed->category_id,
                'is_imported' => true,
                'status' => Article::STATUS_PUBLISHED,
                'published_at' => $this->parsePublishedDate($item['published_at']),
                'user_id' => 1, // Usuario sistema
                'import_metadata' => [
                    'imported_at' => now()->toISOString(),
                    'feed_name' => $feed->name,
                    'original_url' => $item['link'],
                    'has_image' => true,
                ]
            ]);

            // Generar y asignar etiquetas automáticamente
            $this->assignAutoTags($article, $item);

            Log::info('Artículo RSS importado exitosamente', [
                'article_id' => $article->id,
                'title' => $article->title,
                'feed_id' => $feed->id
            ]);

            return 'imported';
            
        } catch (\Illuminate\Database\QueryException $e) {
            // Manejar violaciones de constraints únicos como omisiones
            if ($e->errorInfo[1] == 1062) { // MySQL duplicate entry error
                Log::info('Artículo omitido: duplicado detectado durante creación', [
                    'title' => $item['title'],
                    'external_id' => $item['external_id'],
                    'feed_id' => $feed->id,
                    'constraint' => 'database_level'
                ]);
                return 'skipped';
            }
            
            // Re-lanzar otras excepciones de base de datos
            throw $e;
        }
    }

    /**
     * Procesar imagen destacada del artículo
     */
    private function processFeaturedImage(array $enclosures): ?string
    {
        foreach ($enclosures as $enclosure) {
            $imageUrl = $this->downloadAndProcessImage($enclosure['url']);
            if ($imageUrl) {
                return $imageUrl;
            }
        }
        return null;
    }

    /**
     * Descargar y procesar imagen
     */
    private function downloadAndProcessImage(string $imageUrl): ?string
    {
        try {
            // Validar URL
            if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                return null;
            }

            // Obtener extensión
            $extension = strtolower(pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
            if (!in_array($extension, $this->allowedImageTypes)) {
                // Intentar detectar por headers
                $headers = get_headers($imageUrl, true);
                $contentType = $headers['Content-Type'] ?? '';
                if (!str_starts_with($contentType, 'image/')) {
                    return null;
                }
                $extension = 'jpg'; // Default
            }

            // Descargar imagen
            $response = Http::timeout($this->imageTimeout)->get($imageUrl);
            
            if (!$response->successful()) {
                return null;
            }

            $imageContent = $response->body();
            if (strlen($imageContent) < 1024) { // Muy pequeña
                return null;
            }

            // Generar nombre único
            $filename = 'articles/' . date('Y/m/') . uniqid() . '.' . $extension;

            // Procesar con Intervention Image v3
            $manager = new ImageManager(new Driver());
            $image = $manager->read($imageContent);
            
            // Redimensionar si es muy grande
            if ($image->width() > 1200 || $image->height() > 800) {
                $image = $image->scale(1200, 800);
            }

            // Optimizar y codificar
            $encodedImage = match($extension) {
                'png' => $image->toPng(),
                'webp' => $image->toWebp(85),
                'gif' => $image->toGif(),
                default => $image->toJpeg(85),
            };

            // Guardar en storage
            Storage::disk('public')->put($filename, $encodedImage);

            return $filename;

        } catch (Exception $e) {
            Log::warning('Error procesando imagen', [
                'image_url' => $imageUrl,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Limpiar contenido HTML
     */
    private function cleanHtmlContent(string $content): string
    {
        // Limpiar tags HTML pero conservar estructura básica
        $allowedTags = '<p><br><strong><b><em><i><u><a><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><div><span><img><iframe>';
        $content = strip_tags($content, $allowedTags);
        
        // Limpiar atributos peligrosos
        $content = preg_replace('/(<[^>]*) style="[^"]*"([^>]*>)/', '$1$2', $content);
        $content = preg_replace('/(<[^>]*) class="[^"]*"([^>]*>)/', '$1$2', $content);
        $content = preg_replace('/(<[^>]*) id="[^"]*"([^>]*>)/', '$1$2', $content);
        
        // Convertir entidades HTML
        return html_entity_decode($content, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generar extracto del contenido
     */
    private function generateExcerpt(string $content): string
    {
        $text = strip_tags($content);
        return Str::limit($text, 160);
    }

    /**
     * Generar slug único
     */
    private function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        // Incluir artículos soft-deleted en la verificación
        while (Article::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Parsear fecha de publicación
     */
    private function parsePublishedDate(?string $date): Carbon
    {
        if (!$date) {
            return now();
        }

        try {
            return Carbon::parse($date);
        } catch (Exception $e) {
            return now();
        }
    }

    /**
     * Generar resumen de importación
     */
    private function generateImportSummary(array $results): void
    {
        $totalImported = 0;
        $totalSkipped = 0;
        $totalErrors = 0;
        $successfulFeeds = 0;
        $failedFeeds = 0;

        foreach ($results as $result) {
            if ($result['success']) {
                $successfulFeeds++;
                $totalImported += $result['imported'];
                $totalSkipped += $result['skipped'];
                $totalErrors += $result['errors'];
            } else {
                $failedFeeds++;
                $totalErrors++;
            }
        }

        Log::info('Resumen de importación RSS', [
            'total_feeds' => count($results),
            'successful_feeds' => $successfulFeeds,
            'failed_feeds' => $failedFeeds,
            'total_imported' => $totalImported,
            'total_skipped' => $totalSkipped,
            'total_errors' => $totalErrors,
        ]);
    }

    /**
     * Obtener estadísticas de importación
     */
    public function getImportStats(): array
    {
        return [
            'total_feeds' => RssFeed::count(),
            'active_feeds' => RssFeed::active()->count(),
            'failed_feeds' => RssFeed::failed()->count(),
            'imported_articles_today' => Article::imported()
                ->whereDate('created_at', today())
                ->count(),
            'imported_articles_total' => Article::imported()->count(),
            'last_import' => RssFeed::whereNotNull('last_fetched_at')
                ->orderBy('last_fetched_at', 'desc')
                ->value('last_fetched_at'),
        ];
    }

    /**
     * Agregar crédito de la fuente al final del contenido
     */
    private function addSourceCredit(string $content, string $sourceUrl, string $feedName): string
    {
        // Limpiar y obtener el dominio de la fuente
        $parsedUrl = parse_url($sourceUrl);
        $domain = $parsedUrl['host'] ?? '';
        
        // Remover 'www.' si existe
        $domain = preg_replace('/^www\./', '', $domain);
        
        // Crear el crédito de la fuente
        $sourceCredit = '<hr style="margin: 2rem 0; border: none; border-top: 1px solid #e5e7eb;">' . PHP_EOL;
        $sourceCredit .= '<div style="font-size: 0.875rem; color: #6b7280; padding: 1rem; background-color: #f9fafb; border-left: 4px solid #3b82f6; margin: 1rem 0;">' . PHP_EOL;
        $sourceCredit .= '<p style="margin: 0;"><strong>Fuente:</strong> ';
        $sourceCredit .= '<a href="' . htmlspecialchars($sourceUrl) . '" target="_blank" rel="noopener noreferrer" style="color: #3b82f6; text-decoration: none;">';
        $sourceCredit .= htmlspecialchars($domain);
        $sourceCredit .= '</a></p>' . PHP_EOL;
        $sourceCredit .= '</div>';
        
        return $content . PHP_EOL . $sourceCredit;
    }

    /**
     * Asignar etiquetas automáticamente basado en contenido
     */
    private function assignAutoTags(Article $article, array $item): void
    {
        $tags = $this->generateSmartTags($item, $article);
        
        if (!empty($tags)) {
            $article->tags()->sync($tags);
            
            Log::info('Etiquetas asignadas automáticamente', [
                'article_id' => $article->id,
                'tags_count' => count($tags),
                'tags' => Tag::whereIn('id', $tags)->pluck('name')->toArray()
            ]);
        }
    }

    /**
     * Generar etiquetas inteligentes basadas en el contenido
     */
    private function generateSmartTags(array $item, Article $article): array
    {
        $maxTags = 8;
        $assignedTags = [];
        
        // Combinar título y contenido para análisis
        $fullContent = strtolower($item['title'] . ' ' . strip_tags($item['content'] ?? $item['description'] ?? ''));
        
        // Etiquetas basadas en palabras clave comunes
        $keywordMap = [
            // Urgencia y tiempo
            'urgente' => ['urgente', 'última hora', 'breaking', 'ahora mismo', 'inmediatamente'],
            'en-vivo' => ['en vivo', 'directo', 'live', 'transmisión', 'streaming'],
            
            // Tipo de contenido  
            'exclusiva' => ['exclusiva', 'exclusivo', 'primicia', 'scoop'],
            'entrevista' => ['entrevista', 'conversación', 'diálogo', 'charla'],
            'analisis' => ['análisis', 'evaluación', 'estudio', 'investigación', 'informe'],
            'opinion' => ['opinión', 'editorial', 'columna', 'punto de vista'],
            'reportaje' => ['reportaje', 'crónica', 'documental', 'especial'],
            
            // Deportes específicos
            'futbol' => ['fútbol', 'football', 'soccer', 'gol', 'partido'],
            'beisbol' => ['béisbol', 'baseball', 'pelota', 'jonrón'],
            'baloncesto' => ['baloncesto', 'basketball', 'basquet', 'canasta'],
            
            // Política específica
            'gobierno' => ['gobierno', 'ministro', 'presidente', 'diputado', 'senador'],
            'elecciones' => ['elección', 'voto', 'candidato', 'campaña', 'urnas'],
            
            // Economía específica
            'finanzas' => ['banco', 'crédito', 'inversión', 'bolsa', 'mercado'],
            'empleo' => ['trabajo', 'empleo', 'desempleo', 'salario', 'laboral'],
            
            // Entretenimiento específico
            'celebridades' => ['actor', 'actriz', 'artista', 'famoso', 'celebridad'],
            'musica' => ['música', 'canción', 'album', 'concierto', 'artista'],
            'cine' => ['película', 'cine', 'filme', 'estreno', 'director'],
            
            // Emergencias y eventos
            'emergencia' => ['emergencia', 'accidente', 'incidente', 'crisis', 'alerta'],
            'salud' => ['salud', 'hospital', 'médico', 'enfermedad', 'vacuna'],
            'educacion' => ['educación', 'escuela', 'universidad', 'estudiante', 'profesor'],
            'tecnologia' => ['tecnología', 'internet', 'digital', 'app', 'software'],
        ];
        
        // Buscar palabras clave en el contenido
        foreach ($keywordMap as $tagSlug => $keywords) {
            if (count($assignedTags) >= $maxTags) break;
            
            foreach ($keywords as $keyword) {
                if (str_contains($fullContent, $keyword)) {
                    $tag = $this->findOrCreateTag($tagSlug, ucfirst(str_replace('-', ' ', $tagSlug)));
                    if ($tag && !in_array($tag->id, $assignedTags)) {
                        $assignedTags[] = $tag->id;
                        break; // Solo una vez por categoría
                    }
                }
            }
        }
        
        // Si no encontramos suficientes etiquetas, agregar basadas en la categoría
        if (count($assignedTags) < 3) {
            $categoryTag = $this->getCategoryTag($article->category_id);
            if ($categoryTag && !in_array($categoryTag->id, $assignedTags)) {
                $assignedTags[] = $categoryTag->id;
            }
        }
        
        // Agregar etiquetas genéricas si aún no tenemos suficientes
        if (count($assignedTags) < 2) {
            $genericTag = $this->findOrCreateTag('noticia', 'Noticia');
            if ($genericTag && !in_array($genericTag->id, $assignedTags)) {
                $assignedTags[] = $genericTag->id;
            }
        }
        
        return array_slice($assignedTags, 0, $maxTags);
    }
    
    /**
     * Encontrar o crear etiqueta
     */
    private function findOrCreateTag(string $slug, string $name): ?Tag
    {
        // Primero verificar si la etiqueta ya existe
        $existingTag = Tag::where('slug', $slug)->first();
        if ($existingTag) {
            return $existingTag;
        }

        try {
            return Tag::create([
                'slug' => $slug,
                'name' => $name,
                'is_active' => true,
                'color' => $this->getRandomTagColor()
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Si es un error de duplicado, intentar encontrar la etiqueta existente
            if (str_contains($e->getMessage(), 'Duplicate entry') || $e->getCode() === '23000') {
                $existingTag = Tag::where('slug', $slug)->first();
                if ($existingTag) {
                    Log::debug('Etiqueta duplicada encontrada después de error, usando existente', [
                        'slug' => $slug,
                        'existing_tag_id' => $existingTag->id
                    ]);
                    return $existingTag;
                }
            }
            
            Log::warning('Error creando etiqueta', [
                'slug' => $slug,
                'name' => $name,
                'error' => $e->getMessage()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Error inesperado creando etiqueta', [
                'slug' => $slug,
                'name' => $name,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Obtener etiqueta basada en categoría
     */
    private function getCategoryTag($categoryId): ?Tag
    {
        // Convertir a entero de forma segura
        $categoryId = is_numeric($categoryId) ? (int) $categoryId : null;
        
        if (!$categoryId) {
            return null;
        }
        $categoryTagMap = [
            1 => 'local',      // Noticias Locales
            2 => 'deportes',   // Deportes  
            3 => 'entretenimiento', // Entretenimiento
            4 => 'politica',   // Política
            5 => 'economia',   // Economía
        ];
        
        $tagSlug = $categoryTagMap[$categoryId] ?? null;
        if (!$tagSlug) return null;
        
        return $this->findOrCreateTag($tagSlug, ucfirst($tagSlug));
    }
    
    /**
     * Obtener color aleatorio para etiquetas
     */
    private function getRandomTagColor(): string
    {
        $colors = [
            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
            '#EC4899', '#06B6D4', '#84CC16', '#F97316', '#6366F1'
        ];
        
        return $colors[array_rand($colors)];
    }
    
    /**
     * Limpiar etiquetas huérfanas (sin artículos)
     */
    public function cleanOrphanTags(): int
    {
        $orphanTags = Tag::whereDoesntHave('articles')->get();
        $count = $orphanTags->count();
        
        if ($count > 0) {
            Tag::whereDoesntHave('articles')->delete();
            
            Log::info('Etiquetas huérfanas limpiadas', [
                'deleted_count' => $count,
                'deleted_tags' => $orphanTags->pluck('name')->toArray()
            ]);
        }
        
        return $count;
    }
}
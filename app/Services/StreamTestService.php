<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PlayerUrl;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class StreamTestService
{
    private int $timeout;
    private int $connectionTimeout;
    private array $allowedContentTypes;

    public function __construct()
    {
        $this->timeout = config('streaming.test_timeout', 10);
        $this->connectionTimeout = config('streaming.connection_timeout', 5);
        $this->allowedContentTypes = config('streaming.allowed_content_types', [
            'video/mp4',
            'application/x-mpegURL',
            'video/x-ms-wmv',
            'video/quicktime',
            'application/octet-stream',
            'video/mp2t',
            'application/vnd.apple.mpegurl',
        ]);
    }

    /**
     * Probar conectividad de una URL específica
     */
    public function testUrl(string $url): array
    {
        $startTime = microtime(true);
        
        try {
            // Validar formato de URL
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return $this->createResult(false, 'URL inválida', 0, [
                    'error_type' => 'invalid_url',
                    'url' => $url
                ]);
            }

            // Realizar la petición HTTP
            $response = Http::timeout($this->timeout)
                ->connectTimeout($this->connectionTimeout)
                ->withHeaders([
                    'User-Agent' => 'Tucanaltv Stream Tester/1.0',
                    'Accept' => '*/*'
                ])
                ->get($url);

            $responseTime = (int) ((microtime(true) - $startTime) * 1000);
            
            if ($response->successful()) {
                $contentType = $response->header('content-type');
                $contentLength = $response->header('content-length');
                
                return $this->createResult(
                    true,
                    'Conexión exitosa',
                    $responseTime,
                    [
                        'status_code' => $response->status(),
                        'content_type' => $contentType,
                        'content_length' => $contentLength,
                        'headers' => $this->getRelevantHeaders($response->headers()),
                        'is_streaming_content' => $this->isStreamingContent($contentType),
                    ]
                );
            } else {
                return $this->createResult(
                    false,
                    "Error HTTP {$response->status()}",
                    $responseTime,
                    [
                        'status_code' => $response->status(),
                        'error_type' => 'http_error'
                    ]
                );
            }
            
        } catch (Exception $e) {
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);
            
            return $this->createResult(
                false,
                "Error de conexión: {$e->getMessage()}",
                $responseTime,
                [
                    'error_type' => 'connection_error',
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * Probar una PlayerUrl específica
     */
    public function testPlayerUrl(PlayerUrl $playerUrl): array
    {
        Log::info("Iniciando test de PlayerUrl: {$playerUrl->name}", [
            'id' => $playerUrl->id,
            'url' => $playerUrl->url
        ]);

        // Marcar como en prueba
        $playerUrl->markAsTesting();

        $result = $this->testUrl($playerUrl->url);
        
        // Actualizar resultado en el modelo
        $playerUrl->updateTestResult(
            $result['success'],
            $result['response_time'],
            $result['message']
        );

        // Si el test fue exitoso y la URL no está activa, preguntar si activar
        if ($result['success'] && !$playerUrl->is_active) {
            $result['can_activate'] = true;
        }

        Log::info("Test completado para PlayerUrl: {$playerUrl->name}", [
            'success' => $result['success'],
            'response_time' => $result['response_time'],
            'message' => $result['message']
        ]);

        return $result;
    }

    /**
     * Probar todas las URLs inactivas
     */
    public function testAllInactive(): array
    {
        $results = [];
        $inactiveUrls = PlayerUrl::inactive()->get();
        
        Log::info("Iniciando test masivo de URLs inactivas", [
            'count' => $inactiveUrls->count()
        ]);

        foreach ($inactiveUrls as $playerUrl) {
            $results[$playerUrl->id] = $this->testPlayerUrl($playerUrl);
            
            // Pequeña pausa entre tests para evitar sobrecarga
            usleep(500000); // 0.5 segundos
        }
        
        $successCount = collect($results)->where('success', true)->count();
        
        Log::info("Test masivo completado", [
            'total' => $inactiveUrls->count(),
            'successful' => $successCount,
            'failed' => $inactiveUrls->count() - $successCount
        ]);

        return $results;
    }

    /**
     * Obtener la mejor URL disponible basada en tests recientes
     */
    public function getBestAvailableUrl(): ?PlayerUrl
    {
        // Primero intentar obtener la URL activa si funciona
        $activeUrl = PlayerUrl::getActive();
        if ($activeUrl && $activeUrl->isRecentlyTested(30)) {
            $testResult = json_decode($activeUrl->test_result, true);
            if (is_array($testResult) && ($testResult['success'] ?? false)) {
                return $activeUrl;
            }
        }

        // Buscar la mejor URL basada en tests recientes
        return PlayerUrl::recentlyTested()
            ->where('test_result', 'like', '%success%')
            ->orderBy('test_response_time', 'asc')
            ->first();
    }

    /**
     * Probar conectividad básica sin guardar en base de datos
     */
    public function quickTest(string $url): bool
    {
        try {
            $response = Http::timeout(3)
                ->connectTimeout(2)
                ->head($url);
                
            return $response->successful();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Crear resultado estructurado
     */
    private function createResult(bool $success, string $message, int $responseTime, array $metadata = []): array
    {
        return [
            'success' => $success,
            'message' => $message,
            'response_time' => $responseTime,
            'tested_at' => now()->toISOString(),
            'metadata' => $metadata,
        ];
    }

    /**
     * Verificar si el content-type corresponde a contenido de streaming
     */
    private function isStreamingContent(?string $contentType): bool
    {
        if (!$contentType) {
            return false;
        }

        $contentType = strtolower(trim($contentType));
        
        foreach ($this->allowedContentTypes as $allowedType) {
            if (str_starts_with($contentType, strtolower($allowedType))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtener headers relevantes para debugging
     */
    private function getRelevantHeaders(array $headers): array
    {
        $relevantHeaders = [
            'content-type',
            'content-length',
            'server',
            'cache-control',
            'last-modified',
            'etag',
        ];

        $result = [];
        foreach ($relevantHeaders as $header) {
            if (isset($headers[$header])) {
                $result[$header] = $headers[$header];
            }
        }

        return $result;
    }

    /**
     * Obtener estadísticas de rendimiento de URLs
     */
    public function getPerformanceStats(): array
    {
        return [
            'total_urls' => PlayerUrl::count(),
            'active_urls' => PlayerUrl::active()->count(),
            'recently_tested' => PlayerUrl::recentlyTested()->count(),
            'successful_tests' => PlayerUrl::whereNotNull('test_result')
                ->where('test_result', 'like', '%exitosa%')
                ->count(),
            'average_response_time' => PlayerUrl::whereNotNull('test_response_time')
                ->avg('test_response_time'),
            'fastest_url' => PlayerUrl::whereNotNull('test_response_time')
                ->orderBy('test_response_time', 'asc')
                ->first(),
        ];
    }
}
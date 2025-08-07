<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PlayerUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StreamProxyController extends Controller
{
    /**
     * Proxificar stream activo
     */
    public function activeStream(Request $request): Response
    {
        $activeUrl = PlayerUrl::getActive();
        
        if (!$activeUrl) {
            return response('No active stream configured', 404)
                ->header('Content-Type', 'text/plain');
        }

        return $this->proxyStream($activeUrl->url, $request);
    }

    /**
     * Proxificar stream específico por ID
     */
    public function streamById(int $id, Request $request): Response
    {
        $playerUrl = PlayerUrl::find($id);
        
        if (!$playerUrl) {
            return response('Stream not found', 404)
                ->header('Content-Type', 'text/plain');
        }

        return $this->proxyStream($playerUrl->url, $request);
    }

    /**
     * Proxificar URL externa
     */
    private function proxyStream(string $url, Request $request): Response
    {
        try {
            Log::info('Proxying stream request', [
                'url' => $url,
                'user_agent' => $request->header('User-Agent'),
                'client_ip' => $request->ip()
            ]);

            // Hacer petición al stream original con más configuraciones
            $response = Http::timeout(30)
                ->connectTimeout(10)
                ->retry(2, 1000)
                ->withOptions([
                    'verify' => false, // Disable SSL verification for external streams
                    'allow_redirects' => true,
                ])
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Accept' => 'application/x-mpegURL, application/vnd.apple.mpegurl, application/json, text/plain, */*',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Accept-Encoding' => 'identity',
                    'Connection' => 'keep-alive',
                ])
                ->get($url);

            if (!$response->successful()) {
                Log::warning('Stream proxy failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'error' => $response->body()
                ]);

                return response('Stream temporarily unavailable', 503)
                    ->header('Content-Type', 'text/plain');
            }

            // Determinar content-type
            $contentType = $response->header('Content-Type');
            if (!$contentType) {
                // Inferir del URL o contenido
                if (str_ends_with($url, '.m3u8')) {
                    $contentType = 'application/x-mpegURL';
                } elseif (str_ends_with($url, '.ts')) {
                    $contentType = 'video/mp2t';
                } else {
                    $contentType = 'application/octet-stream';
                }
            }

            // Obtener el contenido de la respuesta
            $body = $response->body();
            
            // Log para debugging
            Log::info('Stream proxy response', [
                'url' => $url,
                'status' => $response->status(),
                'content_type' => $contentType,
                'content_length' => strlen($body),
                'body_preview' => substr($body, 0, 500)
            ]);
            
            // Para playlists M3U8, no procesamos las URLs por ahora para evitar problemas
            // Solo pasamos el contenido directamente
            if (str_contains($contentType, 'mpegURL') || str_ends_with($url, '.m3u8')) {
                // Mantener el playlist original sin modificar por ahora
                Log::info('M3U8 playlist detected, passing through without modification');
            }

            return response($body, 200, [
                'Content-Type' => $contentType,
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
                'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, Authorization',
                'Cache-Control' => $response->header('Cache-Control', 'no-cache'),
                'X-Proxy-Source' => 'TuCanalTV',
            ]);

        } catch (\Exception $e) {
            Log::error('Stream proxy exception', [
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response('Stream proxy error: ' . $e->getMessage(), 500)
                ->header('Content-Type', 'text/plain');
        }
    }

    /**
     * Procesar playlist M3U8 para convertir URLs relativas en URLs de proxy
     */
    private function processM3U8Playlist(string $content, string $originalUrl, Request $request): string
    {
        $baseUrl = dirname($originalUrl);
        $lines = explode("\n", $content);
        $processedLines = [];

        foreach ($lines as $line) {
            $line = trim($line);
            
            // Si la línea es una URL relativa, convertirla a proxy URL
            if (!empty($line) && !str_starts_with($line, '#')) {
                if (!str_starts_with($line, 'http')) {
                    // URL relativa - convertir a URL completa
                    $fullUrl = $baseUrl . '/' . ltrim($line, '/');
                    // Crear proxy URL para segmentos TS
                    $proxyUrl = route('stream.proxy.external', ['url' => base64_encode($fullUrl)]);
                    $processedLines[] = $proxyUrl;
                } else {
                    // URL absoluta - crear proxy URL
                    $proxyUrl = route('stream.proxy.external', ['url' => base64_encode($line)]);
                    $processedLines[] = $proxyUrl;
                }
            } else {
                // Línea de comentario o vacía
                $processedLines[] = $line;
            }
        }

        return implode("\n", $processedLines);
    }

    /**
     * Proxificar URL externa codificada en base64
     */
    public function proxyExternal(string $encodedUrl, Request $request): Response
    {
        $url = base64_decode($encodedUrl);
        
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return response('Invalid URL', 400);
        }

        return $this->proxyStream($url, $request);
    }

    /**
     * Manejar peticiones OPTIONS para CORS
     */
    public function options(): Response
    {
        return response('', 200, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
            'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, Authorization',
            'Access-Control-Max-Age' => '86400',
        ]);
    }
}
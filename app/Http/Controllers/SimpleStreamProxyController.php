<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PlayerUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SimpleStreamProxyController extends Controller
{
    /**
     * Simple proxy that just adds CORS headers without modifying content
     */
    public function proxy(Request $request): Response
    {
        $activeUrl = PlayerUrl::getActive();
        
        if (!$activeUrl) {
            return $this->corsResponse('No active stream', 404);
        }

        return $this->streamContent($activeUrl->url, $request);
    }

    /**
     * Stream content with CORS headers
     */
    private function streamContent(string $url, Request $request): Response
    {
        try {
            Log::info('Simple proxy request', ['url' => $url]);

            // Make request to original stream
            $response = Http::timeout(30)
                ->withOptions(['verify' => false])
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; TuCanalTV-Proxy/1.0)',
                    'Accept' => '*/*',
                ])
                ->get($url);

            if (!$response->successful()) {
                Log::warning('Simple proxy failed', [
                    'url' => $url,
                    'status' => $response->status()
                ]);
                return $this->corsResponse('Stream unavailable', 503);
            }

            // Determine content type
            $contentType = $response->header('Content-Type') ?? 'application/x-mpegURL';
            
            // Return with CORS headers
            return $this->corsResponse($response->body(), 200, [
                'Content-Type' => $contentType,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);

        } catch (\Exception $e) {
            Log::error('Simple proxy exception', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return $this->corsResponse('Proxy error', 500);
        }
    }

    /**
     * Response with CORS headers
     */
    private function corsResponse(string $content, int $status = 200, array $headers = []): Response
    {
        $corsHeaders = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
            'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept',
            'Access-Control-Max-Age' => '86400',
        ];

        return response($content, $status, array_merge($corsHeaders, $headers));
    }

    /**
     * Handle OPTIONS requests
     */
    public function options(): Response
    {
        return $this->corsResponse('', 200);
    }
}
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestStreamUrl extends Command
{
    protected $signature = 'stream:test-url {url : URL to test}';
    
    protected $description = 'Test a specific stream URL for compatibility';

    public function handle(): int
    {
        $url = $this->argument('url');
        
        $this->info("🔍 Testing stream URL: {$url}");
        $this->newLine();

        // Test 1: Basic connectivity
        $this->line("1️⃣ Testing basic connectivity...");
        try {
            $response = Http::timeout(10)->head($url);
            
            if ($response->successful()) {
                $this->info("   ✅ HTTP {$response->status()} - Accessible");
                
                $headers = $response->headers();
                $this->line("   Content-Type: " . ($headers['content-type'][0] ?? 'Not specified'));
                $this->line("   Content-Length: " . ($headers['content-length'][0] ?? 'Not specified'));
                
                // Check CORS headers
                if (isset($headers['access-control-allow-origin'])) {
                    $this->info("   ✅ CORS: " . $headers['access-control-allow-origin'][0]);
                } else {
                    $this->warn("   ⚠️  No CORS headers found");
                }
            } else {
                $this->error("   ❌ HTTP {$response->status()} - Not accessible");
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Connection failed: " . $e->getMessage());
            return Command::FAILURE;
        }

        $this->newLine();

        // Test 2: Content retrieval
        $this->line("2️⃣ Testing content retrieval...");
        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; VideoJS)',
                    'Accept' => 'application/x-mpegURL, */*'
                ])
                ->get($url);
            
            if ($response->successful()) {
                $content = $response->body();
                $this->info("   ✅ Content retrieved (" . strlen($content) . " bytes)");
                
                // Analyze M3U8 content
                if (str_contains($content, '#EXTM3U')) {
                    $this->info("   ✅ Valid M3U8 playlist detected");
                    
                    $lines = explode("\n", $content);
                    $urls = array_filter($lines, function($line) {
                        return !str_starts_with(trim($line), '#') && !empty(trim($line));
                    });
                    
                    $this->line("   📊 Contains " . count($urls) . " stream URLs");
                    
                    // Show first few URLs
                    $firstUrls = array_slice($urls, 0, 3);
                    foreach ($firstUrls as $streamUrl) {
                        $trimmed = trim($streamUrl);
                        if (str_starts_with($trimmed, 'http')) {
                            $this->line("   🔗 " . $trimmed);
                        } else {
                            $this->line("   🔗 " . dirname($url) . '/' . ltrim($trimmed, '/'));
                        }
                    }
                } else {
                    $this->warn("   ⚠️  Content doesn't appear to be a valid M3U8 playlist");
                    $this->line("   Preview: " . substr($content, 0, 200) . '...');
                }
            } else {
                $this->error("   ❌ Failed to retrieve content: HTTP {$response->status()}");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Content retrieval failed: " . $e->getMessage());
        }

        $this->newLine();

        // Test 3: Browser compatibility
        $this->line("3️⃣ Browser compatibility analysis...");
        
        if (str_ends_with($url, '.m3u8')) {
            $this->info("   ✅ HLS format (.m3u8) - Compatible with Video.js");
        }
        
        if (str_starts_with($url, 'https://')) {
            $this->info("   ✅ HTTPS - Secure connection");
        } else {
            $this->warn("   ⚠️  HTTP - May have mixed content issues on HTTPS sites");
        }

        $this->newLine();
        $this->info("🏁 Stream URL analysis complete!");

        return Command::SUCCESS;
    }
}
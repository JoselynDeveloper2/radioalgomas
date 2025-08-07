<?php

namespace App\Console\Commands;

use App\Models\Player;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestStreamCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stream:test {url?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test stream URL accessibility';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url') ?: Player::getActiveStreamUrl();
        
        $this->info("Testing stream URL: {$url}");
        
        try {
            $response = Http::timeout(10)->head($url);
            
            if ($response->successful()) {
                $this->info('✅ Stream URL is accessible');
                $this->info("Status: {$response->status()}");
                
                $headers = $response->headers();
                if (isset($headers['Content-Type'])) {
                    $this->info("Content-Type: " . implode(', ', $headers['Content-Type']));
                }
            } else {
                $this->error("❌ Stream URL returned status: {$response->status()}");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error testing stream: {$e->getMessage()}");
        }
        
        // Also test with curl
        $this->info("\nTesting with cURL...");
        $curlCommand = "curl -I --max-time 10 \"{$url}\"";
        $this->info("Command: {$curlCommand}");
        
        return 0;
    }
}

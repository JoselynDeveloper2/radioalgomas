<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\PlayerUrl;
use Illuminate\Console\Command;

class CreateTestUrl extends Command
{
    protected $signature = 'stream:create-test-url';
    
    protected $description = 'Create a test URL for CORS testing';

    public function handle(): int
    {
        $url = PlayerUrl::create([
            'name' => 'Test Stream CORS',
            'url' => 'https://www.tdtchannels.com/lists/tv.m3u8',
            'status' => PlayerUrl::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        $this->info("✅ URL creada exitosamente:");
        $this->line("   ID: {$url->id}");
        $this->line("   Nombre: {$url->name}");
        $this->line("   URL: {$url->url}");
        $this->line("   Proxy URL: " . route('stream.proxy.active'));

        return Command::SUCCESS;
    }
}
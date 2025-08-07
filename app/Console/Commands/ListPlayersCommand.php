<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ListPlayersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'players:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all stream players in database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $players = \App\Models\Player::all();
        
        if ($players->isEmpty()) {
            $this->info('No players found in database.');
            return 0;
        }
        
        $this->info('Stream Players:');
        $this->line('');
        
        foreach ($players as $player) {
            $status = $player->is_active ? '✅ ACTIVO' : '❌ Inactivo';
            $this->line("ID: {$player->id}");
            $this->line("Nombre: {$player->name}");
            $this->line("Estado: {$status}");
            $this->line("URL: {$player->stream_url}");
            if ($player->backup_url) {
                $this->line("Backup: {$player->backup_url}");
            }
            $this->line("Descripción: {$player->description}");
            $this->line('');
        }
        
        return 0;
    }
}

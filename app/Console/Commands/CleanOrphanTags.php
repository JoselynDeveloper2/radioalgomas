<?php

namespace App\Console\Commands;

use App\Services\RssImportService;
use Illuminate\Console\Command;

class CleanOrphanTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tags:clean
                          {--dry-run : Solo mostrar las etiquetas que se eliminarían}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpiar etiquetas huérfanas (sin artículos asociados)';

    /**
     * Execute the console command.
     */
    public function handle(RssImportService $rssService): int
    {
        $this->info('🧹 Limpiando etiquetas huérfanas...');
        
        if ($this->option('dry-run')) {
            $this->info('Modo simulación (dry-run) - No se eliminarán etiquetas');
            
            $orphanTags = \App\Models\Tag::whereDoesntHave('articles')->get();
            
            if ($orphanTags->isEmpty()) {
                $this->info('✅ No se encontraron etiquetas huérfanas');
                return Command::SUCCESS;
            }
            
            $this->warn("Se encontraron {$orphanTags->count()} etiquetas huérfanas:");
            
            $headers = ['ID', 'Nombre', 'Slug', 'Creada'];
            $rows = $orphanTags->map(function ($tag) {
                return [
                    $tag->id,
                    $tag->name,
                    $tag->slug,
                    $tag->created_at->format('Y-m-d H:i:s')
                ];
            })->toArray();
            
            $this->table($headers, $rows);
            
            return Command::SUCCESS;
        }
        
        $deletedCount = $rssService->cleanOrphanTags();
        
        if ($deletedCount > 0) {
            $this->info("✅ Se eliminaron {$deletedCount} etiquetas huérfanas");
        } else {
            $this->info('✅ No se encontraron etiquetas huérfanas para eliminar');
        }
        
        return Command::SUCCESS;
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->foreignId('rss_feed_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_id')->nullable()->index(); // Para evitar duplicados
            $table->text('source_url')->nullable(); // URL original del artículo
            $table->boolean('is_imported')->default(false); // Marca si fue importado vía RSS
            $table->json('import_metadata')->nullable(); // Metadata de importación
            
            // Índice compuesto para evitar duplicados por feed
            $table->unique(['rss_feed_id', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['rss_feed_id']);
            $table->dropIndex(['external_id']);
            $table->dropUnique(['rss_feed_id', 'external_id']);
            $table->dropColumn([
                'rss_feed_id', 
                'external_id', 
                'source_url', 
                'is_imported', 
                'import_metadata'
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Este ajuste de charset solo aplica a MySQL
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Asegurar que las tablas principales usen utf8mb4 para soportar emojis y caracteres especiales
        $tables = ['articles', 'categories', 'tags'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE `{$table}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                // Específicamente para campos de texto que pueden contener caracteres especiales
                if ($table === 'articles') {
                    DB::statement("ALTER TABLE `{$table}` MODIFY `meta_keywords` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    DB::statement("ALTER TABLE `{$table}` MODIFY `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    DB::statement("ALTER TABLE `{$table}` MODIFY `content` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    DB::statement("ALTER TABLE `{$table}` MODIFY `excerpt` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No revertir cambios de charset ya que podría causar pérdida de datos
        // Los cambios de charset son seguros de mantener
    }
};

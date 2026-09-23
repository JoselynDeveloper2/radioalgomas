<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nota importada que la redacción trabajó: su canónica pasa a ser la del propio sitio.
        Schema::table('articles', function (Blueprint $table) {
            $table->boolean('is_editorial')->default(false)->after('is_imported');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('is_editorial');
        });
    }
};

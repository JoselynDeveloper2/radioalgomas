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
        Schema::create('player_urls', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('url');
            $table->boolean('is_active')->default(false);
            $table->enum('status', ['active', 'inactive', 'testing'])->default('inactive');
            $table->json('metadata')->nullable(); // Para almacenar información adicional como formato, bitrate, etc.
            $table->timestamp('last_tested_at')->nullable();
            $table->text('test_result')->nullable(); // Resultado del último test
            $table->integer('test_response_time')->nullable(); // Tiempo de respuesta en ms
            $table->text('notes')->nullable(); // Notas adicionales
            $table->timestamps();
            
            // Índices para mejor performance
            $table->index('is_active');
            $table->index('status');
            $table->index(['is_active', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_urls');
    }
};

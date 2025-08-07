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
        Schema::create('rss_feeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('url');
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(1);
            $table->timestamp('last_fetched_at')->nullable();
            $table->enum('status', ['active', 'failed', 'testing'])->default('active');
            $table->integer('success_count')->default(0);
            $table->integer('error_count')->default(0);
            $table->text('last_error')->nullable();
            $table->json('settings')->nullable(); // Para configuraciones adicionales
            $table->timestamps();
            
            $table->index(['is_active', 'priority']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rss_feeds');
    }
};

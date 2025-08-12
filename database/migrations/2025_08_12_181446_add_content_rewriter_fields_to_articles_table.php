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
            $table->longText('original_content')->nullable()->after('content');
            $table->longText('rewritten_content')->nullable()->after('original_content');
            $table->string('seo_title')->nullable()->after('meta_title');
            $table->text('seo_meta_description')->nullable()->after('meta_description');
            $table->string('seo_canonical_url')->nullable()->after('canonical_url');
            $table->string('content_hash', 64)->nullable()->index()->after('seo_canonical_url');
            $table->boolean('is_original')->default(true)->after('content_hash');
            $table->integer('publish_delay')->default(0)->after('is_original'); // minutos
            $table->timestamp('scheduled_publish_at')->nullable()->after('publish_delay');
            $table->enum('rewrite_status', ['pending', 'processing', 'completed', 'failed'])->default('pending')->after('scheduled_publish_at');
            $table->timestamp('content_rewritten_at')->nullable()->after('rewrite_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn([
                'original_content',
                'rewritten_content', 
                'seo_title',
                'seo_meta_description',
                'seo_canonical_url',
                'content_hash',
                'is_original',
                'publish_delay',
                'scheduled_publish_at',
                'rewrite_status',
                'content_rewritten_at'
            ]);
        });
    }
};

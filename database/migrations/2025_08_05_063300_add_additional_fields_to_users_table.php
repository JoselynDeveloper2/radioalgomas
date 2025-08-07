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
        Schema::table('users', function (Blueprint $table) {
            $table->text('bio')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('bio');
            $table->string('website')->nullable()->after('avatar');
            $table->string('twitter')->nullable()->after('website');
            $table->string('linkedin')->nullable()->after('twitter');
            $table->boolean('is_active')->default(true)->after('linkedin');
            $table->enum('role', ['admin', 'editor', 'author', 'subscriber'])->default('subscriber')->after('is_active');
            
            // Índices
            $table->index('role');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['is_active']);
            $table->dropColumn([
                'bio',
                'avatar',
                'website',
                'twitter',
                'linkedin',
                'is_active',
                'role'
            ]);
        });
    }
};

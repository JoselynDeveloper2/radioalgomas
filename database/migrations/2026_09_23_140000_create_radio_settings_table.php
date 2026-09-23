<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fila única: configuración general del player de radio, editable desde el administrador.
        Schema::create('radio_settings', function (Blueprint $table) {
            $table->id();
            $table->string('station_name');
            $table->string('stream_url');
            $table->boolean('autoplay')->default(true);
            $table->string('timezone')->default('UTC');
            $table->string('fallback_show_name');
            $table->string('fallback_show_host');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radio_settings');
    }
};

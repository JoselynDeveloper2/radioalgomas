<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radio_shows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('host');
            // "HH:MM" en la zona horaria de radio_settings.timezone.
            $table->string('start_time', 5);
            $table->string('end_time', 5);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radio_shows');
    }
};

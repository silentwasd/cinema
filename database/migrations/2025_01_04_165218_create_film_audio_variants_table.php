<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('film_audio_variants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('film_id')
                  ->constrained()
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->string('name');
            $table->bigInteger('bitrate')->unsigned();
            $table->integer('index')->unsigned();
            $table->string('language');
            $table->boolean('is_default')->unsigned()->default(false);
            $table->string('status')->default('to-process');
            $table->string('path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('film_audio_variants');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('film_video_variants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('film_id')
                  ->constrained()
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->string('name');
            $table->bigInteger('bitrate')->unsigned();
            $table->integer('crf')->unsigned();
            $table->integer('width')->unsigned();
            $table->integer('height')->unsigned();
            $table->string('status')->default('to-process');
            $table->string('path')->nullable();
            $table->boolean('to_sdr')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('film_video_variants');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('country_film', function (Blueprint $table) {
            $table->id();

            $table->foreignId('country_id')
                  ->constrained()
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->foreignId('film_id')
                  ->constrained()
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_film');
    }
};

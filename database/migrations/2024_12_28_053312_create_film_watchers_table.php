<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('film_watchers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('film_id')
                  ->constrained()
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->foreignId('watcher_id')
                  ->constrained('users')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->string('status')->default('to-watch');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('film_watchers');
    }
};

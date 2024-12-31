<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('film_people', function (Blueprint $table) {
            $table->id();

            $table->foreignId('film_id')
                  ->constrained()
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->foreignId('person_id')
                  ->constrained('people')
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->string('role');
            $table->string('role_details')->nullable();
            $table->bigInteger('order_id')->unsigned();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('film_people');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->foreignId('country_id')
                  ->nullable()
                  ->after('photo')
                  ->constrained('countries')
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropConstrainedForeignId('country_id');
        });
    }
};

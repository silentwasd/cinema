<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->string('original_name')->nullable()->after('name');
            $table->date('birth_date')->nullable()->after('original_name');
            $table->date('death_date')->nullable()->after('birth_date');
        });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropColumn(['original_name', 'birth_date', 'death_date']);
        });
    }
};

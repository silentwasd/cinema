<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('film_video_variants', function (Blueprint $table) {
            $table->string('input_path', 1024)->nullable()->after('name');
            $table->integer('progress')->unsigned()->default(0)->after('input_path');
        });
    }

    public function down(): void
    {
        Schema::table('film_video_variants', function (Blueprint $table) {
            $table->dropColumn(['input_path', 'progress']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('films', function (Blueprint $table) {
            $table->date('release_date')->nullable()->after('released_at');
        });

        foreach (DB::table('films')->get() as $film) {
            DB::table('films')->where('id', $film->id)->update(['release_date' => new DateTime($film->released_at)]);
        }

        Schema::table('films', function (Blueprint $table) {
            $table->dropColumn('released_at');
        });
    }

    public function down(): void
    {
        Schema::table('films', function (Blueprint $table) {
            $table->timestamp('released_at')->nullable()->after('release_date');
        });

        foreach (DB::table('films')->get() as $film) {
            DB::table('films')->where('id', $film->id)->update(['released_at' => (new DateTime($film->release_date))->getTimestamp()]);
        }

        Schema::table('films', function (Blueprint $table) {
            $table->dropColumn('release_date');
        });
    }
};

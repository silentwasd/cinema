<?php

namespace App\Models;

use App\Enums\FilmWatchStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FilmWatcher extends Model
{
    protected $fillable = [
        'film_id',
        'status'
    ];

    protected $casts = [
        'watch_status' => FilmWatchStatus::class
    ];

    public function film(): BelongsTo
    {
        return $this->belongsTo(Film::class);
    }

    public function watcher(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

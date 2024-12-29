<?php

namespace App\Models;

use App\Enums\FilmFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Film extends Model
{
    protected $fillable = [
        'name',
        'cover',
        'release_date',
        'description',
        'format'
    ];

    protected $casts = [
        'release_date' => 'immutable_datetime',
        'format'       => FilmFormat::class
    ];

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function watchers(): HasMany
    {
        return $this->hasMany(FilmWatcher::class);
    }
}

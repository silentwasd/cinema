<?php

namespace App\Models;

use App\Enums\FilmVideoVariantStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FilmVideoVariant extends Model
{
    protected $fillable = [
        'name',
        'bitrate',
        'crf',
        'width',
        'height',
        'to_sdr'
    ];

    protected $casts = [
        'status' => FilmVideoVariantStatus::class,
        'to_sdr' => 'boolean'
    ];

    public function film(): BelongsTo
    {
        return $this->belongsTo(Film::class);
    }
}

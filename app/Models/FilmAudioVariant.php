<?php

namespace App\Models;

use App\Enums\FilmAudioVariantStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FilmAudioVariant extends Model
{
    protected $fillable = [
        'name',
        'language',
        'bitrate',
        'index',
        'is_default',
        'input_path'
    ];

    protected $casts = [
        'status'     => FilmAudioVariantStatus::class,
        'is_default' => 'boolean'
    ];

    public function film(): BelongsTo
    {
        return $this->belongsTo(Film::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    protected $fillable = [
        'user_id',
        'film_id',
        'data'
    ];

    protected $casts = [
        'data' => AsCollection::class
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

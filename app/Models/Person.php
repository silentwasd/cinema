<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    protected $fillable = [
        'name',
        'photo',
        'author_id',
        'country_id'
    ];

    public function films(): HasMany
    {
        return $this->hasMany(FilmPerson::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}

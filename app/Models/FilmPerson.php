<?php

namespace App\Models;

use App\Enums\PersonRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FilmPerson extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'film_id',
        'person_id',
        'role',
        'role_details',
        'order_id'
    ];

    protected $casts = [
        'role' => PersonRole::class
    ];

    public function film(): BelongsTo
    {
        return $this->belongsTo(Film::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}

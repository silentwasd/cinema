<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Film extends Model
{
    protected $fillable = [
        'name',
        'cover',
        'release_date',
        'description',
        'list_id'
    ];

    protected $casts = [
        'release_date' => 'immutable_datetime'
    ];

    public function lists(): BelongsToMany
    {
        return $this->belongsToMany(ListModel::class, 'film_list_user', 'film_id', 'list_id');
    }

    public function listUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'film_list_user', 'film_id', 'user_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }
}

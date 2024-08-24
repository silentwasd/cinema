<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ListModel extends Model
{
    protected $table = 'lists';

    protected $fillable = [
        'name'
    ];

    public function films(): BelongsToMany
    {
        return $this->belongsToMany(Film::class, 'film_list_user', 'list_id', 'film_id');
    }
}

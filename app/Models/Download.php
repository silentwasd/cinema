<?php

namespace App\Models;

use App\Enums\DownloadStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Download extends Model
{
    protected $fillable = [
        'url'
    ];

    protected $casts = [
        'status' => DownloadStatus::class
    ];

    public function film(): HasOne
    {
        return $this->hasOne(Film::class);
    }
}

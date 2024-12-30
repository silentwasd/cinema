<?php

namespace App\Http\Resources\Management;

use App\Models\FilmWatcher;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin FilmWatcher */
class FilmWatcherResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id,
            'film'   => new FilmResource($this->film),
            'status' => $this->status
        ];
    }
}

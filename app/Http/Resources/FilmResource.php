<?php

namespace App\Http\Resources;

use App\Models\Film;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Film */
class FilmResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'format'       => $this->format,
            'cover'        => $this->cover,
            'release_date' => $this->release_date?->format('d.m.Y'),
            'description'  => $this->description,
            'is_mine'      => $this->watchers()->where('watcher_id', $request->user()?->id)->exists()
        ];
    }
}

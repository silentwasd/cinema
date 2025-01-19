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
            'id'       => $this->id,
            'film'     => new FilmResource($this->whenLoaded('film')),
            'status'   => $this->status,
            'reaction' => $this->whenLoaded('film', fn() => $this->film->feedbacks()->where('user_id', auth()->id())->first()?->reaction ?? 0)
        ];
    }
}

<?php

namespace App\Http\Resources\Management;

use App\Enums\UserRole;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Person */
class PersonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'original_name' => $this->original_name,
            'birth_date'    => $this->birth_date?->format('Y-m-d'),
            'death_date'    => $this->death_date?->format('Y-m-d'),
            'photo'         => $this->photo,
            'can_edit'      => $request->user() && ($request->user()->role == UserRole::Admin || $this->author_id == $request->user()->id),
            'country'       => new CountryResource($this->whenLoaded('country')),
            'films_count'   => $this->whenCounted('films'),
            'roles'         => $this->whenLoaded('films', fn() => $this->films->pluck('role')->unique()->values()),
            'films'         => FilmPersonResource::collection($this->whenLoaded('films'))
        ];
    }
}

<?php

namespace App\Http\Resources\Management;

use App\Models\FilmPerson;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin FilmPerson */
class FilmPersonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'film_id'      => $this->film_id,
            'person_id'    => $this->person_id,
            'role'         => $this->role,
            'role_details' => $this->role_details,
            'order_id'     => $this->order_id,
            'person'       => new PersonResource($this->whenLoaded('person')),
            'film'         => new FilmResource($this->whenLoaded('film'))
        ];
    }
}

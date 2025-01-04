<?php

namespace App\Http\Resources\Cinema;

use App\Models\Film;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Film */
class FilmResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'cover'          => $this->cover,
            'video_variants' => FilmVideoVariantResource::collection($this->whenLoaded('videoVariants'))
        ];
    }
}

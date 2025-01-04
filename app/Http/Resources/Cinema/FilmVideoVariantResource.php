<?php

namespace App\Http\Resources\Cinema;

use App\Models\FilmVideoVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin FilmVideoVariant */
class FilmVideoVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name'   => $this->name,
            'height' => $this->height
        ];
    }
}

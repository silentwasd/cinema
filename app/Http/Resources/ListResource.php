<?php

namespace App\Http\Resources;

use App\Models\ListModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ListModel */
class ListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'films' => $this->films()->distinct()->count('film_id')
        ];
    }
}

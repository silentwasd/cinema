<?php

namespace App\Http\Resources\Management;

use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Rating */
class RatingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'user'       => new UserResource($this->user),
            'data'       => $this->data,
            'created_at' => $this->created_at->getTimestampMs()
        ];
    }
}

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
            'id'       => $this->id,
            'name'     => $this->name,
            'photo'    => $this->photo,
            'can_edit' => ($request->user()->role == UserRole::Admin) ||
                          (
                              $this->author_id == $request->user()->id
                          )
        ];
    }
}

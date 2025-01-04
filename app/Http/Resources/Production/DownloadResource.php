<?php

namespace App\Http\Resources\Production;

use App\Models\Download;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Download */
class DownloadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'hash'     => $this->hash,
            'status'   => $this->status,
            'progress' => $this->progress
        ];
    }
}

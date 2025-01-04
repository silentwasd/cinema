<?php

namespace App\Http\Resources\Production;

use App\Models\FilmAudioVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin FilmAudioVariant */
class FilmAudioVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'bitrate'    => $this->bitrate,
            'index'      => $this->index,
            'language'   => $this->language,
            'status'     => $this->status,
            'is_default' => $this->is_default,
            'path'       => $this->path ? ($this->path . '.m3u8') : null
        ];
    }
}

<?php

namespace App\Http\Resources\Production;

use App\Models\FilmVideoVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin FilmVideoVariant */
class FilmVideoVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'bitrate'  => $this->bitrate,
            'crf'      => $this->crf,
            'width'    => $this->width,
            'height'   => $this->height,
            'status'   => $this->status,
            'path'     => $this->path ? ($this->path . '.m3u8') : null,
            'to_sdr'   => $this->to_sdr,
            'progress' => $this->progress
        ];
    }
}

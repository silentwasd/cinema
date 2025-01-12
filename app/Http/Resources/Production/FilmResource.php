<?php

namespace App\Http\Resources\Production;

use App\Models\Film;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Film */
class FilmResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'name'                 => $this->name,
            'cover'                => $this->cover,
            'release_date'         => $this->release_date?->getTimestampMs(),
            'cinema_status'        => $this->cinema_status,
            'video_variants_count' => $this->whenCounted('videoVariants'),
            'audio_variants_count' => $this->whenCounted('audioVariants'),
            'has_download'         => !!$this->download
        ];
    }
}

<?php

namespace App\Http\Resources\Production;

use App\Enums\FilmAudioVariantStatus;
use App\Enums\FilmVideoVariantStatus;
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
            'has_download'         => !!$this->download,
            'is_video_ready'       => $this->whenLoaded('videoVariants', fn() => $this->videoVariants->filter(fn($variant) => $variant->status == FilmVideoVariantStatus::Completed)->count() > 0),
            'is_audio_ready'       => $this->whenLoaded('audioVariants', fn() => $this->audioVariants->filter(fn($variant) => $variant->status == FilmAudioVariantStatus::Completed)->count() > 0)
        ];
    }
}

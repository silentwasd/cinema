<?php

namespace App\Http\Controllers\Cinema;

use App\Enums\FilmAudioVariantStatus;
use App\Enums\FilmCinemaStatus;
use App\Enums\FilmVideoVariantStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Cinema\FilmResource;
use App\Models\Film;
use Illuminate\Support\Str;

class FilmController extends Controller
{
    public function show(Film $film)
    {
        $film->load(['videoVariants']);

        return new FilmResource($film);
    }

    public function watch(Film $film)
    {
        if ($film->cinema_status != FilmCinemaStatus::Published)
            abort(404);

        if (!$film->videoVariants()->where('status', FilmVideoVariantStatus::Completed)->exists() ||
            !$film->audioVariants()->where('status', FilmAudioVariantStatus::Completed)->exists()) {
            abort(404);
        }

        $videos = $film->videoVariants()
                       ->where('status', FilmVideoVariantStatus::Completed)
                       ->orderBy('height', 'desc')
                       ->get();

        $audios = $film->audioVariants()
                       ->where('status', FilmAudioVariantStatus::Completed)
                       ->orderBy('is_default', 'desc')
                       ->get();

        $content = "#EXTM3U\n#EXT-X-VERSION:3";

        foreach ($videos as $video) {
            $path    = config('services.movie.storage_url') . '/' . $video->path;
            $content .= "\n#EXT-X-STREAM-INF:BANDWIDTH=$video->bitrate,RESOLUTION={$video->width}x$video->height,AUDIO=\"audio\"\n$path.m3u8";
        }

        foreach ($audios as $audio) {
            $path       = config('services.movie.storage_url') . '/' . $audio->path;
            $autoselect = $audio->is_default ? 'YES' : 'NO';
            $name       = addslashes(Str::replace('"', "", $audio->name));
            $content    .= "\n#EXT-X-MEDIA:TYPE=AUDIO,GROUP-ID=\"audio\",NAME=\"$name\",LANGUAGE=\"$audio->language\",AUTOSELECT=$autoselect,DEFAULT=$autoselect,URI=\"$path.m3u8\"";
        }

        return response($content);
    }
}

<?php

namespace App\Http\Controllers\Production;

use App\Enums\FilmAudioVariantStatus;
use App\Enums\FilmCinemaStatus;
use App\Enums\FilmVideoVariantStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Production\FilmResource;
use App\Models\Film;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class FilmController extends Controller
{
    public function index()
    {
        return FilmResource::collection(
            Film::where('cinema_status', '!=', FilmCinemaStatus::NotAvailable)->get()
        );
    }

    public function show(Film $film)
    {
        $name = $film->download->name;

        $path = config('services.transmission.downloads') . '/' . $name;

        if (!file_exists($path)) {
            abort(404, 'Path of downloaded film not found.');
        }

        $slashedPath = addslashes($path);

        $result = Process::run("ffprobe -v quiet -print_format json -show_format -show_streams \"$slashedPath\"");

        $info = json_decode($result->output(), true);

        $film->loadCount(['videoVariants', 'audioVariants']);

        return (new FilmResource($film))->additional([
            'raw'  => $info,
            'info' => [
                'video' => [
                    'width'    => $info['streams'][0]['width'],
                    'height'   => $info['streams'][0]['height'],
                    'bitrate'  => $info['format']['bit_rate'],
                    'has_hdr'  => isset($info['streams'][0]['color_space']) ? Str::startsWith($info['streams'][0]['color_space'], 'bt2020') : false,
                    'duration' => $info['format']['duration']
                ],
                'audio' => collect($info['streams'])
                    ->filter(fn($stream) => $stream['codec_type'] == 'audio')
                    ->map(fn($stream) => [
                        'index'   => $stream['index'],
                        'bitrate' => $stream['bit_rate'],
                        ...isset($stream['tags']['title']) ? ['title' => $stream['tags']['title']] : [],
                        ...isset($stream['tags']['language']) ? ['language' => $stream['tags']['language']] : [],
                    ])
                    ->values()
            ]
        ]);
    }

    public function update(Film $film)
    {
        $film->cinema_status = FilmCinemaStatus::Published;
        $film->save();
    }

    public function watch(Film $film)
    {
        if (!$film->videoVariants()->where('status', FilmVideoVariantStatus::Completed)->exists() ||
            !$film->audioVariants()->where('status', FilmAudioVariantStatus::Completed)->exists()) {
            abort(404, 'Video or audio variant not found.');
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

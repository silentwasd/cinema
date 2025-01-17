<?php

namespace App\Http\Controllers\Production;

use App\Enums\FilmAudioVariantStatus;
use App\Enums\FilmCinemaStatus;
use App\Enums\FilmVideoVariantStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Production\FilmResource;
use App\Models\Film;
use App\Services\ProductionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class FilmController extends Controller
{
    public function index()
    {
        return FilmResource::collection(
            Film::where('cinema_status', '!=', FilmCinemaStatus::NotAvailable)
                ->with('download')
                ->withCount(['videoVariants', 'audioVariants'])
                ->orderByRaw("FIELD(cinema_status, 'preparing', 'published')")
                ->get()
        );
    }

    public function show(Request $request, ProductionService $production, Film $film)
    {
        $data = $request->validate([
            'file' => 'nullable|string'
        ]);

        $files = collect();
        $path  = null;

        if ($film->download) {
            $name = $film->download->name;
            $path = config('services.transmission.downloads') . '/' . $name;

            if (!file_exists($path))
                abort(404, 'Path of downloaded film not found.');

            if (File::isDirectory($path)) {
                $files = collect(File::allFiles($path))
                    ->filter(fn(SplFileInfo $file) => in_array($file->getExtension(), [
                        'mp4',
                        'mkv',
                        'mov',
                        'avi',
                        'm2ts'
                    ]));

                $path = $files->filter(fn(SplFileInfo $file) => ($data['file'] ?? false) ? $file->getFilename() == $data['file'] : true)
                              ->first()
                              ->getPathname();
            } else {
                $files = collect(File::files(config('services.transmission.downloads')))->filter(fn(SplFileInfo $file) => $file->getFilename() == $name);
            }

            $info = $production->getData($path);
        }

        $film->loadCount(['videoVariants', 'audioVariants']);
        $film->load(['videoVariants', 'audioVariants']);

        return (new FilmResource($film))->additional([
            ...$film->download ? [
                'info'         => [
                    'video' => [
                        'width'    => $info['streams'][0]['width'] ?? 0,
                        'height'   => $info['streams'][0]['height'] ?? 0,
                        'bitrate'  => $info['format']['bit_rate'] ?? 0,
                        'has_hdr'  => isset($info['streams'][0]['color_space']) ? Str::startsWith($info['streams'][0]['color_space'], 'bt2020') : false,
                        'duration' => $info['format']['duration'] ?? 0
                    ],
                    'audio' => collect($info['streams'])
                        ->filter(fn($stream) => $stream['codec_type'] == 'audio' && (isset($stream['bit_rate']) || isset($stream['tags']['BPS'])))
                        ->map(fn($stream) => [
                            'index'   => $stream['index'],
                            'bitrate' => $stream['bit_rate'] ?? $stream['tags']['BPS'],
                            ...isset($stream['tags']['title']) ? ['title' => $stream['tags']['title']] : [],
                            ...isset($stream['tags']['language']) ? ['language' => $stream['tags']['language']] : [],
                        ])
                        ->values()
                ],
                'files'        => $files->map(fn(SplFileInfo $file) => $file->getFilename())->values(),
                'selectedFile' => $files->filter(fn(SplFileInfo $file) => Str::replace('\\', '/', $file->getPathname()) == $path)->first()->getFilename()
            ] : []
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

    public function destroy(Film $film)
    {
        if ($film->cinema_status == FilmCinemaStatus::Preparing)
            $film->cinema_status = FilmCinemaStatus::NotAvailable;

        $film->download_id = null;
        $film->save();
    }
}

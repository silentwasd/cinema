<?php

namespace App\Http\Controllers\Production;

use App\Enums\FilmVideoVariantStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Production\FilmVideoVariantResource;
use App\Jobs\ProcessFilmVideoVariantJob;
use App\Models\Film;
use App\Models\FilmVideoVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class FilmVideoVariantController extends Controller
{
    public function index(Film $film)
    {
        return FilmVideoVariantResource::collection(
            $film->videoVariants
        );
    }

    public function store(Request $request, Film $film)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'bitrate' => 'required|integer|min:0',
            'crf'     => 'required|integer|min:0',
            'width'   => 'required|integer|min:0',
            'height'  => 'required|integer|min:0',
            'has_hdr' => 'required|boolean'
        ]);

        $videoVariant = $film->videoVariants()->create([
            ...$data,
            'to_sdr' => $data['has_hdr']
        ]);

        ProcessFilmVideoVariantJob::dispatch($videoVariant)
                                  ->onQueue('ffmpeg');
    }

    public function update(Film $film, FilmVideoVariant $videoVariant)
    {
        $videoVariant->status = FilmVideoVariantStatus::ToProcess;
        $videoVariant->save();

        ProcessFilmVideoVariantJob::dispatch($videoVariant)
                                  ->onQueue('ffmpeg');
    }

    public function preview(Request $request, Film $film)
    {
        $data = $request->validate([
            'bitrate' => 'required|integer|min:0',
            'crf'     => 'required|integer|min:0',
            'width'   => 'required|integer|min:0',
            'height'  => 'required|integer|min:0',
            'has_hdr' => 'required|boolean'
        ]);

        $name = $film->download->name;

        $path = config('services.transmission.downloads') . '/' . $name;

        if (!file_exists($path)) {
            abort(404, 'Path of downloaded film not found.');
        }

        $slashedPath = addslashes($path);

        $previewPath = sys_get_temp_dir() . '/' . Str::random(32) . '.mp4';

        $dblBitrate = $data['bitrate'] * 2;

        if ($data['has_hdr']) {
            $result = Process::timeout(2 * 60)->run("ffmpeg -ss 00:05:00 -i \"$slashedPath\" -t 00:00:10 -map 0:0 -c:v libx264 -b:v:0 {$data['bitrate']} -vf \"zscale=t=linear:npl=100,format=gbrpf32le,zscale=p=bt709,tonemap=tonemap=hable:desat=0,zscale=t=bt709:m=bt709:r=tv,format=yuv420p\" -crf {$data['crf']} -maxrate {$data['bitrate']} -bufsize $dblBitrate -s {$data['width']}x{$data['height']} -ac 2 $previewPath");
        } else {
            $result = Process::timeout(2 * 60)->run("ffmpeg -ss 00:05:00 -i \"$slashedPath\" -t 00:00:10 -map 0:0 -c:v libx264 -b:v:0 {$data['bitrate']} -crf {$data['crf']} -maxrate {$data['bitrate']} -bufsize $dblBitrate -s {$data['width']}x{$data['height']} -ac 2 $previewPath");
        }

        if ($result->successful())
            return response()->download($previewPath);

        abort(500, $result->errorOutput());
    }
}

<?php

namespace App\Http\Controllers\Production;

use App\Enums\FilmVideoVariantStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Production\FilmVideoVariantResource;
use App\Jobs\ProcessFilmVideoVariantJob;
use App\Models\Film;
use App\Models\FilmVideoVariant;
use App\Services\ProductionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FilmVideoVariantController extends Controller
{
    public function index(Film $film)
    {
        return FilmVideoVariantResource::collection(
            $film->videoVariants
        );
    }

    public function store(Request $request, ProductionService $production, Film $film)
    {
        $data = $request->validate([
            'file'    => 'required|string',
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

        ProcessFilmVideoVariantJob::dispatch($videoVariant, $production->getPath($film->download, $data['file']))
                                  ->onQueue('ffmpeg');
    }

    public function update(Film $film, FilmVideoVariant $videoVariant)
    {
        $videoVariant->status = FilmVideoVariantStatus::ToProcess;
        $videoVariant->save();

        ProcessFilmVideoVariantJob::dispatch($videoVariant)
                                  ->onQueue('ffmpeg');
    }

    public function preview(Request $request, ProductionService $production, Film $film)
    {
        $data = $request->validate([
            'file'    => 'required|string',
            'bitrate' => 'required|integer|min:0',
            'crf'     => 'required|integer|min:0',
            'width'   => 'required|integer|min:0',
            'height'  => 'required|integer|min:0',
            'has_hdr' => 'required|boolean'
        ]);

        if (!($path = $production->getPath($film->download, $data['file'])))
            abort(404, 'Path of downloaded film not found.');

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

    public function destroy(Film $film, FilmVideoVariant $videoVariant)
    {
        $m3u8 = $videoVariant->path . '.m3u8';

        $content = Storage::disk('public')->get($m3u8);

        if (!preg_match_all("/(\w{8}-\w{4}-\w{4}-\w{4}-\w{12}_\d{3}\.ts)/", $content, $matches))
            abort(404, "Can't parse m3u8 file.");

        foreach ($matches[1] as $match) {
            Storage::disk('public')->delete('streams/' . $match);
        }

        Storage::disk('public')->delete($m3u8);

        $videoVariant->delete();
    }
}

<?php

namespace App\Http\Controllers\Production;

use App\Enums\FilmVideoVariantStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Production\FilmVideoVariantResource;
use App\Jobs\ProcessFilmVideoVariantJob;
use App\Models\Film;
use App\Models\FilmVideoVariant;
use App\Services\Production\VideoProducer;
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
            'to_sdr'     => $data['has_hdr'],
            'input_path' => $production->getPath($film->download, $data['file'])
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

    public function preview(Request $request, ProductionService $production, VideoProducer $producer, Film $film)
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

        $previewPath = sys_get_temp_dir() . '/' . Str::random(32) . '.mp4';

        $result = $producer->input($path)
                           ->codec(config('producer.video_codec'))
                           ->bitrate($data['bitrate'])
                           ->crf($data['crf'])
                           ->resolution($data['width'], $data['height'])
                           ->output($previewPath)
                           ->timeout(60)
                           ->start(minutes: 5)
                           ->duration(seconds: 10)
                           ->toSdr($data['has_hdr'])
                           ->produce();

        if ($result->successful())
            return response()->download($previewPath);

        abort(500, $result->errorOutput());
    }

    public function destroy(Film $film, FilmVideoVariant $videoVariant)
    {
        $m3u8 = $videoVariant->path . '.m3u8';

        $content = Storage::disk('public')->get($m3u8);

        if (!preg_match_all("/(\w{8}-\w{4}-\w{4}-\w{4}-\w{12}_\d{3,4}\.ts)/", $content, $matches))
            abort(404, "Can't parse m3u8 file.");

        foreach ($matches[1] as $match) {
            Storage::disk('public')->delete('streams/' . $match);
        }

        Storage::disk('public')->delete($m3u8);

        $videoVariant->delete();
    }
}

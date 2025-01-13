<?php

namespace App\Http\Controllers\Production;

use App\Enums\FilmAudioVariantStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Production\FilmAudioVariantResource;
use App\Jobs\ProcessFilmAudioVariantJob;
use App\Models\Film;
use App\Models\FilmAudioVariant;
use App\Services\ProductionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class FilmAudioVariantController extends Controller
{
    public function index(Film $film)
    {
        return FilmAudioVariantResource::collection(
            $film->audioVariants
        );
    }

    public function store(Request $request, ProductionService $production, Film $film)
    {
        $data = $request->validate([
            'file'       => 'required|string',
            'title'      => 'required|string|max:255',
            'language'   => 'required|string|max:255',
            'bitrate'    => 'required|integer|min:0',
            'index'      => 'required|integer|min:0',
            'is_default' => 'nullable|boolean'
        ]);

        $audioVariant = $film->audioVariants()->create([
            ...$data,
            'name' => $data['title']
        ]);

        ProcessFilmAudioVariantJob::dispatch($audioVariant, $production->getPath($film->download, $data['file']))
                                  ->onQueue('ffmpeg');
    }

    public function update(Film $film, FilmAudioVariant $audioVariant)
    {
        $audioVariant->status = FilmAudioVariantStatus::ToProcess;
        $audioVariant->save();

        ProcessFilmAudioVariantJob::dispatch($audioVariant)
                                  ->onQueue('ffmpeg');
    }

    public function markAsDefault(Film $film, FilmAudioVariant $audioVariant)
    {
        $film->audioVariants()->update(['is_default' => false]);

        $audioVariant->is_default = true;
        $audioVariant->save();
    }

    public function preview(Request $request, ProductionService $production, Film $film)
    {
        $data = $request->validate([
            'file'    => 'required|string',
            'index'   => 'required|integer|min:0',
            'bitrate' => 'required|integer|min:0'
        ]);

        if (!($path = $production->getPath($film->download, $data['file'])))
            abort(404, 'Path of downloaded film not found.');

        $slashedPath = addslashes($path);

        $previewPath = sys_get_temp_dir() . '/' . Str::random(32) . '.mp4';

        $result = Process::run("ffmpeg -ss 00:05:00 -i \"$slashedPath\" -t 00:01:00 -map 0:{$data['index']} -c:a aac -b:a {$data['bitrate']} -ac 2 $previewPath");

        if ($result->successful())
            return response()->download($previewPath);

        abort(500, $result->errorOutput());
    }
}

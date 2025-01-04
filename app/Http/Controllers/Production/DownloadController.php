<?php

namespace App\Http\Controllers\Production;

use App\Enums\DownloadStatus;
use App\Enums\FilmCinemaStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Production\DownloadResource;
use App\Jobs\DownloadWaitNameJob;
use App\Models\Download;
use App\Models\Film;
use Illuminate\Http\Request;
use Transmission\Transmission;

class DownloadController extends Controller
{
    public function index()
    {
        return DownloadResource::collection(Download::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'url' => 'required|string|max:255'
        ]);

        $download = Download::create($data);

        $transmission = new Transmission();
        $torrent      = $transmission->add($download['url']);

        $download->hash     = $torrent->getHash();
        $download->progress = $torrent->getPercentDone();
        $download->status   = DownloadStatus::Stopped;
        $download->save();

        DownloadWaitNameJob::dispatch($download)
                           ->delay(now()->addSeconds(10));
    }

    public function update(Request $request, Download $download)
    {
        $data = $request->validate([
            'film_id' => 'required|exists:films,id'
        ]);

        $film = Film::findOrFail($data['film_id']);

        $film->cinema_status = FilmCinemaStatus::Preparing;
        $film->download_id   = $download->id;
        $film->save();
    }
}

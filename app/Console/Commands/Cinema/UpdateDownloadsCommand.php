<?php

namespace App\Console\Commands\Cinema;

use App\Enums\DownloadStatus;
use App\Models\Download;
use Illuminate\Console\Command;
use Transmission\Model\Torrent;
use Transmission\Transmission;

class UpdateDownloadsCommand extends Command
{
    protected $signature = 'cinema:update-downloads';

    protected $description = 'Update downloads';

    public function handle(): void
    {
        foreach (Download::all() as $download) {
            $download     = Download::findOrFail($download->id);
            $transmission = new Transmission();

            $torrents = $transmission->all();

            /** @var Torrent $torrent */
            $torrent  = collect($torrents)->first(fn($torrent) => $torrent->getHash() == $download->hash);

            if (!$torrent) {
                $download->status   = DownloadStatus::Stopped;
                $download->progress = 0;
                $download->save();
                continue;
            }

            $download->name     = $torrent->getName();
            $download->status   = DownloadStatus::fromInt($torrent->getStatus());
            $download->progress = $torrent->getPercentDone();
            $download->save();
        }
    }
}

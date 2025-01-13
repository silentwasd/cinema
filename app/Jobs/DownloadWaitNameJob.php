<?php

namespace App\Jobs;

use App\Enums\DownloadStatus;
use App\Models\Download;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Transmission\Transmission;

class DownloadWaitNameJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Download $download)
    {
    }

    public function handle(): void
    {
        $transmission = new Transmission();

        $torrents = $transmission->all();

        foreach ($torrents as $torrent) {
            if ($torrent->getHash() == $this->download->hash) {
                if ($torrent->getName() == $torrent->getHash())
                    return;

                $this->download->name     = $torrent->getName();
                $this->download->status   = DownloadStatus::fromInt($torrent->getStatus());
                $this->download->progress = $torrent->getPercentDone();
                $this->download->save();

                return;
            }
        }
    }
}

<?php

namespace App\Jobs;

use App\Enums\FilmVideoVariantStatus;
use App\Models\FilmVideoVariant;
use App\Services\Production\VideoProducer;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessFilmVideoVariantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public FilmVideoVariant $videoVariant
    )
    {
    }

    /**
     * @throws Exception
     */
    public function handle(VideoProducer $producer): void
    {
        $path = $this->videoVariant->input_path;

        if (!file_exists($path)) {
            throw new Exception("File $path doesn't exist");
        }

        $this->videoVariant->status = FilmVideoVariantStatus::Processing;
        $this->videoVariant->save();

        Storage::disk('public')->makeDirectory('streams');

        $this->videoVariant->path = 'streams/' . Str::uuid();
        $this->videoVariant->save();

        $result = $producer->input($path)
                           ->codec(config('producer.video_codec'))
                           ->bitrate($this->videoVariant->bitrate)
                           ->crf($this->videoVariant->crf)
                           ->resolution($this->videoVariant->width, $this->videoVariant->height)
                           ->output(Storage::disk('public')->path($this->videoVariant->path))
                           ->toSdr($this->videoVariant->to_sdr)
                           ->asHls()
                           ->produce(function (int $progress) {
                               $this->videoVariant->progress = $progress;
                               $this->videoVariant->save();
                           });

        if (!$result->successful()) {
            throw new Exception($result->errorOutput());
        }

        $this->videoVariant->status = FilmVideoVariantStatus::Completed;
        $this->videoVariant->save();
    }

    public function failed(Exception $exception): void
    {
        $this->videoVariant->status = FilmVideoVariantStatus::Failed;
        $this->videoVariant->save();
    }
}

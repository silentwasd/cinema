<?php

namespace App\Jobs;

use App\Enums\FilmAudioVariantStatus;
use App\Models\FilmAudioVariant;
use App\Services\Production\AudioProducer;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessFilmAudioVariantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public FilmAudioVariant $audioVariant
    )
    {
    }

    /**
     * @throws Exception
     */
    public function handle(AudioProducer $producer): void
    {
        $path = $this->audioVariant->input_path;

        if (!file_exists($path))
            throw new Exception("File $path doesn't exist");

        $this->audioVariant->status = FilmAudioVariantStatus::Processing;
        $this->audioVariant->save();

        Storage::disk('public')->makeDirectory('streams');

        $this->audioVariant->path = 'streams/' . Str::uuid();
        $this->audioVariant->save();

        $result = $producer->input($path)
                           ->codec('aac')
                           ->index($this->audioVariant->index)
                           ->bitrate($this->audioVariant->bitrate)
                           ->output(Storage::disk('public')->path($this->audioVariant->path))
                           ->asHls()
                           ->produce(function (int $progress) {
                               $this->audioVariant->progress = $progress;
                               $this->audioVariant->save();
                           });

        if (!$result->successful()) {
            throw new Exception($result->errorOutput());
        }

        $this->audioVariant->status = FilmAudioVariantStatus::Completed;
        $this->audioVariant->save();
    }

    public function failed(Exception $exception): void
    {
        $this->audioVariant->status = FilmAudioVariantStatus::Failed;
        $this->audioVariant->save();
    }
}

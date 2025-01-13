<?php

namespace App\Jobs;

use App\Enums\FilmAudioVariantStatus;
use App\Models\FilmAudioVariant;
use App\Services\ProductionService;
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
    public function handle(ProductionService $production): void
    {
        $path = $this->audioVariant->input_path;

        if (!file_exists($path))
            throw new Exception("File $path doesn't exist");

        $this->audioVariant->status = FilmAudioVariantStatus::Processing;
        $this->audioVariant->save();

        $slashedInputPath = addslashes($path);

        Storage::disk('public')->makeDirectory('streams');

        $shortOutputPath   = 'streams/' . Str::uuid();
        $slashedOutputPath = addslashes(Storage::disk('public')->path($shortOutputPath));

        $this->audioVariant->path = $shortOutputPath;
        $this->audioVariant->save();

        $data = $production->getData($path);

        $result = Process::timeout(0)
                         ->run(
                             "ffmpeg -i \"$slashedInputPath\" -map 0:{$this->audioVariant->index} -c:a aac -b:a {$this->audioVariant->bitrate} -ac 2 -f hls -hls_time 10 -hls_playlist_type vod -hls_segment_filename \"{$slashedOutputPath}_%03d.ts\" \"$slashedOutputPath.m3u8\"",
                             function (string $type, string $output) use (&$data) {
                                 if (!preg_match("/time=(\d{2}:\d{2}:\d{2}\.\d{2})/", $output, $matches))
                                     return;

                                 sscanf($matches[1], "%d:%d:%f", $hours, $minutes, $seconds);
                                 $totalSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;

                                 $this->audioVariant->progress = floor($totalSeconds / $data['format']['duration'] * 100);
                                 $this->audioVariant->save();
                             }
                         );

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

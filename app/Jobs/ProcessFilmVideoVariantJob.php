<?php

namespace App\Jobs;

use App\Enums\FilmVideoVariantStatus;
use App\Models\FilmVideoVariant;
use App\Services\ProductionService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
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
    public function handle(ProductionService $production): void
    {
        $path = $this->videoVariant->input_path;

        if (!file_exists($path)) {
            throw new Exception("File $path doesn't exist");
        }

        $this->videoVariant->status = FilmVideoVariantStatus::Processing;
        $this->videoVariant->save();

        $slashedInputPath = addslashes($path);

        Storage::disk('public')->makeDirectory('streams');

        $shortOutputPath   = 'streams/' . Str::uuid();
        $slashedOutputPath = addslashes(Storage::disk('public')->path($shortOutputPath));

        $this->videoVariant->path = $shortOutputPath;
        $this->videoVariant->save();

        $dblBitrate = $this->videoVariant->bitrate * 2;

        $data = $production->getData($path);

        if ($this->videoVariant->to_sdr) {
            $result = Process::timeout(0)
                             ->run(
                                 "ffmpeg -ss 00:05:00 -i \"$slashedInputPath\" -t 00:01:00 -map 0:0 -c:v libx264 -b:v:0 {$this->videoVariant->bitrate} -vf \"zscale=t=linear:npl=100,format=gbrpf32le,zscale=p=bt709,tonemap=tonemap=hable:desat=0,zscale=t=bt709:m=bt709:r=tv,format=yuv420p\" -crf {$this->videoVariant->crf} -maxrate {$this->videoVariant->bitrate} -bufsize $dblBitrate -s {$this->videoVariant->width}x{$this->videoVariant->height} -ac 2 -f hls -hls_time 10 -hls_playlist_type vod -hls_segment_filename \"{$slashedOutputPath}_%03d.ts\" \"$slashedOutputPath.m3u8\"",
                                 function (string $type, string $output) use (&$data) {
                                     if (!preg_match("/time=(\d{2}:\d{2}:\d{2}\.\d{2})/", $output, $matches))
                                         return;

                                     sscanf($matches[1], "%d:%d:%f", $hours, $minutes, $seconds);
                                     $totalSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;

                                     $this->videoVariant->progress = floor($totalSeconds / $data['format']['duration'] * 100);
                                     $this->videoVariant->save();
                                 }
                             );
        } else {
            $result = Process::timeout(0)
                             ->run(
                                 "ffmpeg -i \"$slashedInputPath\" -map 0:0 -c:v libx264 -b:v:0 {$this->videoVariant->bitrate} -crf {$this->videoVariant->crf} -maxrate {$this->videoVariant->bitrate} -bufsize $dblBitrate -s {$this->videoVariant->width}x{$this->videoVariant->height} -ac 2 -f hls -hls_time 10 -hls_playlist_type vod -hls_segment_filename \"{$slashedOutputPath}_%03d.ts\" \"$slashedOutputPath.m3u8\"",
                                 function (string $type, string $output) use (&$data) {
                                     if (!preg_match("/time=(\d{2}:\d{2}:\d{2}\.\d{2})/", $output, $matches))
                                         return;

                                     sscanf($matches[1], "%d:%d:%f", $hours, $minutes, $seconds);
                                     $totalSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;

                                     $this->videoVariant->progress = floor($totalSeconds / $data['format']['duration'] * 100);
                                     $this->videoVariant->save();
                                 }
                             );
        }

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

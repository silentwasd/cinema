<?php

namespace App\Jobs;

use App\Enums\FilmVideoVariantStatus;
use App\Models\FilmVideoVariant;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
    public function handle(): void
    {
        $name = $this->videoVariant->film->download->name;

        $path = config('services.transmission.downloads') . '/' . $name;

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

        if ($this->videoVariant->to_sdr) {
            $result = Process::timeout(0)->run("ffmpeg -i \"$slashedInputPath\" -map 0:0 -c:v libx264 -b:v:0 {$this->videoVariant->bitrate} -vf \"zscale=t=linear:npl=100,format=gbrpf32le,zscale=p=bt709,tonemap=tonemap=hable:desat=0,zscale=t=bt709:m=bt709:r=tv,format=yuv420p\" -crf {$this->videoVariant->crf} -maxrate {$this->videoVariant->bitrate} -bufsize $dblBitrate -s {$this->videoVariant->width}x{$this->videoVariant->height} -ac 2 -f hls -hls_time 10 -hls_playlist_type vod -hls_segment_filename \"{$slashedOutputPath}_%03d.ts\" \"$slashedOutputPath.m3u8\"");
        } else {
            $result = Process::timeout(0)->run("ffmpeg -i \"$slashedInputPath\" -map 0:0 -c:v libx264 -b:v:0 {$this->videoVariant->bitrate} -crf {$this->videoVariant->crf} -maxrate {$this->videoVariant->bitrate} -bufsize $dblBitrate -s {$this->videoVariant->width}x{$this->videoVariant->height} -ac 2 -f hls -hls_time 10 -hls_playlist_type vod -hls_segment_filename \"{$slashedOutputPath}_%03d.ts\" \"$slashedOutputPath.m3u8\"");
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

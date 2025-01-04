<?php

namespace App\Console\Commands\Cinema;

use Illuminate\Console\Command;

class MakeHlsCommand extends Command
{
    protected $signature = 'cinema:make-hls {path} {out} {bitrate} {crf} {resolution}';

    protected $description = 'Make HLS video with a preset bitrate, constant rate factor and resolution.';

    public function handle(): void
    {
        $path       = $this->argument('path');
        $out        = $this->argument('out');
        $bitrate    = $this->argument('bitrate');
        $crf        = $this->argument('crf');
        $resolution = $this->argument('resolution');

        $dblBitrate = $bitrate * 2;

        $command = "ffmpeg -i \"$path\" -t 00:01:00 -map 0:v:0 -c:v: libx264 -b:v:0 {$bitrate}k -crf $crf -maxrate {$bitrate}k -bufsize {$dblBitrate}k -s $resolution -f hls -hls_time 10 -hls_playlist vod -hls_segment_filename \"{$out}_%03d.ts\" \"$out.m3u8\"";

        $this->info(`$command`);
    }
}

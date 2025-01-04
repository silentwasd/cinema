<?php

namespace App\Console\Commands\Cinema;

use Illuminate\Console\Command;

class GetVideoParametersCommand extends Command
{
    protected $signature = 'cinema:get-video-parameters {path}';

    protected $description = 'Get video parameters: width, height and bitrate.';

    public function handle(): void
    {
        $path = $this->argument('path');

        $resolution = shell_exec("ffprobe -v error -show_entries stream=width,height -of csv=p=0 \"$path\"");
        $bitrate    = `ffprobe -v error -show_entries format=bit_rate -of csv=p=0 "$path"`;

        $width  = 0;
        $height = 0;

        if (preg_match('/(\d+),(\d+)/', $resolution, $matches)) {
            $width  = $matches[1];
            $height = $matches[2];
        }

        $this->info($width . 'x' . $height . ' ~ ' . round($bitrate / 1024) . ' KB/s');
    }
}

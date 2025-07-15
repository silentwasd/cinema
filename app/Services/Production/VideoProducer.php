<?php

namespace App\Services\Production;

use App\Services\ProductionService;
use Illuminate\Process\ProcessResult;
use Illuminate\Support\Facades\Process;

class VideoProducer
{
    private string $input = '';
    private string $start = '';
    private string $duration = '';
    private string $codec = '';
    private int $bitrate = 0;
    private int $crf = 0;
    private int $width = 0;
    private int $height = 0;
    private string $output = '';
    private int $timeout = 0;
    private bool $sdrFilter = false;
    private bool $asHls = false;

    public function __construct(
        private readonly ProductionService $production
    )
    {
    }

    public function input(string $path): self
    {
        $this->input = $path;
        return $this;
    }

    public function start(int $seconds = 0, int $minutes = 0, int $hours = 0): self
    {
        $this->start = "$hours:$minutes:$seconds";
        return $this;
    }

    public function duration(int $seconds = 0, int $minutes = 0, int $hours = 0): self
    {
        $this->duration = "$hours:$minutes:$seconds";
        return $this;
    }

    public function codec(string $codec): self
    {
        $this->codec = $codec;
        return $this;
    }

    public function bitrate(int $bitrate): self
    {
        $this->bitrate = $bitrate;
        return $this;
    }

    public function crf(int $crf): self
    {
        $this->crf = $crf;
        return $this;
    }

    public function resolution(int $width, int $height): self
    {
        $this->width  = $width;
        $this->height = $height;
        return $this;
    }

    public function output(string $path): self
    {
        $this->output = $path;
        return $this;
    }

    public function timeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function toSdr(bool $value = true): self
    {
        $this->sdrFilter = $value;
        return $this;
    }

    public function asHls(bool $value = true): self
    {
        $this->asHls = $value;
        return $this;
    }

    public function produce(callable $progressCbk = null): ProcessResult
    {
        $command = 'ffmpeg';

        $inputData = $this->production->getData($this->input);

        if ($this->start)
            $command .= ' -ss ' . $this->start;

        if ($this->input)
            $command .= ' -i ' . escapeshellarg($this->input);

        if ($this->duration)
            $command .= ' -t ' . $this->duration;

        $videoStreamIndex = collect($inputData['streams'])->filter(fn($s) => $s['codec_type'] == 'video')->keys()[0];

        $command .= ' -map 0:' . $videoStreamIndex;

        if ($this->codec)
            $command .= ' -c:v ' . $this->codec;

        if ($this->bitrate)
            $command .= ' -b:v ' . $this->bitrate . ' -maxrate ' . $this->bitrate . ' -bufsize ' . ($this->bitrate * 2);

        if ($this->crf)
            $command .= ' -crf ' . $this->crf;

        if ($this->width && $this->height)
            $command .= ' -s ' . $this->width . 'x' . $this->height;

        if ($this->sdrFilter)
            $command .= ' -vf "zscale=t=linear:npl=100,format=gbrpf32le,zscale=p=bt709,tonemap=tonemap=hable:desat=0,zscale=t=bt709:m=bt709:r=tv,format=yuv420p"';

        if ($this->asHls)
            $command .= ' -f hls -hls_time 10 -hls_playlist_type vod -hls_segment_filename ' . escapeshellarg($this->output . '_%04d.ts');

        if ($this->output && !$this->asHls)
            $command .= ' ' . escapeshellarg($this->output);
        else if ($this->output && $this->asHls)
            $command .= ' ' . escapeshellarg($this->output . '.m3u8');

        return Process::timeout($this->timeout)->run(
            $command,
            $progressCbk
                ? function (string $type, string $output) use ($progressCbk, &$inputData) {
                if (!preg_match("/time=(\d{2}:\d{2}:\d{2}\.\d{2})/", $output, $matches))
                    return;

                sscanf($matches[1], "%d:%d:%f", $hours, $minutes, $seconds);
                $totalSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;

                $progressCbk(floor($totalSeconds / $inputData['format']['duration'] * 100));
            }
                : null
        );
    }
}

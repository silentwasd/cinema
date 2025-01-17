<?php

namespace App\Services\Production;

use App\Services\ProductionService;
use Illuminate\Process\ProcessResult;
use Illuminate\Support\Facades\Process;

class AudioProducer
{
    private string $input = '';
    private string $start = '';
    private int $index = 0;
    private string $duration = '';
    private string $codec = '';
    private int $bitrate = 0;
    private string $output = '';
    private int $timeout = 0;
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

    public function index(int $index): self
    {
        $this->index = $index;
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

    public function asHls(bool $value = true): self
    {
        $this->asHls = $value;
        return $this;
    }

    public function produce(callable $progressCbk = null): ProcessResult
    {
        $command = 'ffmpeg';

        if ($this->start)
            $command .= ' -ss ' . $this->start;

        if ($this->input)
            $command .= ' -i "' . addslashes($this->input) . '"';

        if ($this->duration)
            $command .= ' -t ' . $this->duration;

        $command .= ' -map 0:' . $this->index;

        if ($this->codec)
            $command .= ' -c:a ' . $this->codec;

        if ($this->bitrate)
            $command .= ' -b:a ' . $this->bitrate;

        $command .= ' -ac 2';

        if ($this->asHls)
            $command .= ' -f hls -hls_time 10 -hls_playlist_type vod -hls_segment_filename "' . addslashes($this->output) . '_%04d.ts"';

        if ($this->output && !$this->asHls)
            $command .= ' "' . addslashes($this->output) . '"';
        else if ($this->output && $this->asHls)
            $command .= ' "' . addslashes($this->output) . '.m3u8"';

        if ($progressCbk)
            $inputData = $this->production->getData($this->input);
        else
            $inputData = null;

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

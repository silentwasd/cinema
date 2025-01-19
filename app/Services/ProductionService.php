<?php

namespace App\Services;

use App\Models\Download;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Finder\SplFileInfo;

class ProductionService
{
    public function getPath(Download $download, string $file): string|bool
    {
        $name = $download->name;

        $path = config('services.transmission.downloads') . '/' . $name;

        if (!file_exists($path))
            return false;

        if (File::isDirectory($path)) {
            $video = $this->getVideos($path)->first(fn(SplFileInfo $_file) => $_file->getFilename() == $file);

            if (!$video)
                return false;

            return $video->getPathname();
        }

        return $path;
    }

    public function getData(string $path): array
    {
        $slashedPath = addslashes($path);
        $result      = Process::run("ffprobe -v quiet -print_format json -show_format -show_streams \"$slashedPath\"");
        return json_decode($result->output(), true);
    }

    public function getVideos(string $path): Collection
    {
        return collect(File::allFiles($path))
            ->filter(fn(SplFileInfo $file) => in_array($file->getExtension(), [
                'mp4',
                'mkv',
                'mov',
                'avi',
                'm2ts'
            ]));
    }
}

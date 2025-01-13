<?php

namespace App\Services;

use App\Models\Download;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class ProductionService
{
    public function getPath(Download $download, string $file): string|bool
    {
        $name = $download->name;

        $path = config('services.transmission.downloads') . '/' . $name;

        if (!file_exists($path))
            return false;

        if (File::isDirectory($path))
            return $path . '/' . $file;

        return $path;
    }

    public function getData(string $path): array
    {
        $slashedPath = addslashes($path);
        $result      = Process::run("ffprobe -v quiet -print_format json -show_format -show_streams \"$slashedPath\"");
        return json_decode($result->output(), true);
    }
}

<?php

namespace App\Services;

use App\Models\Download;
use Illuminate\Support\Facades\File;

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
}

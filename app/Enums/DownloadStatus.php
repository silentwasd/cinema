<?php

namespace App\Enums;

enum DownloadStatus: string
{
    case Stopped        = 'stopped';
    case CheckQueued    = 'check-queued';
    case Checking       = 'checking';
    case DownloadQueued = 'download-queued';
    case Downloading    = 'downloading';
    case SeedQueued     = 'seed-queued';
    case Seeding        = 'seeding';

    public static function fromInt(int $int): self
    {
        return match ($int) {
            0 => self::Stopped,
            1 => self::CheckQueued,
            2 => self::Checking,
            3 => self::DownloadQueued,
            4 => self::Downloading,
            5 => self::SeedQueued,
            6 => self::Seeding
        };
    }
}

<?php

namespace App\Enums;

enum FilmAudioVariantStatus: string
{
    case ToProcess  = 'to-process';
    case Processing = 'processing';
    case Completed  = 'completed';
    case Failed     = 'failed';
}

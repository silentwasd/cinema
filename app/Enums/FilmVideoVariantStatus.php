<?php

namespace App\Enums;

enum FilmVideoVariantStatus: string
{
    case ToProcess  = 'to-process';
    case Processing = 'processing';
    case Completed  = 'completed';
    case Failed     = 'failed';
}

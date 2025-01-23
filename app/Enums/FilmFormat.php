<?php

namespace App\Enums;

enum FilmFormat: string
{
    case Film          = 'film';
    case MiniSeries    = 'mini-series';
    case Series        = 'series';
    case Cartoon       = 'cartoon';
    case CartoonSeries = 'cartoon-series';
}

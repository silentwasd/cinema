<?php

namespace App\Enums;

enum FilmWatchStatus: string
{
    case ToWatch    = 'to-watch';
    case MustFinish = 'must-finish';
    case Watched    = 'watched';
    case Dropped    = 'dropped';
}

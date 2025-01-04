<?php

namespace App\Enums;

enum FilmCinemaStatus: string
{
    case NotAvailable = 'not-available';
    case Preparing    = 'preparing';
    case Published    = 'published';
}

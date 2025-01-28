<?php

namespace App\Http\Controllers\Public;

use App\Enums\FilmCinemaStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Public\FilmResource;
use App\Models\Film;

class FilmController extends Controller
{
    public function index()
    {
        return FilmResource::collection([
            ...Film::whereNotNull('cover')
                   ->whereNotNull('release_date')
                   ->where('cinema_status', FilmCinemaStatus::Published)
                   ->latest('release_date')
                   ->get()
        ]);
    }
}

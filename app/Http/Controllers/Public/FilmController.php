<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\FilmResource;
use App\Models\Film;

class FilmController extends Controller
{
    public function index()
    {
        $films = Film::whereNotNull('cover')
                     ->whereNotNull('release_date')
                     ->inRandomOrder()
                     ->take(20)
                     ->get();

        return FilmResource::collection($films);
    }
}

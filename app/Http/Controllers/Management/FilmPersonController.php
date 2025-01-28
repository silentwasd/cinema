<?php

namespace App\Http\Controllers\Management;

use App\Enums\PersonRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\Management\FilmPersonResource;
use App\Models\Film;
use App\Models\FilmPerson;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FilmPersonController extends Controller
{
    public function index(Film $film)
    {
        return FilmPersonResource::collection(
            $film->people()->orderBy('order_id')->get()
        );
    }
}

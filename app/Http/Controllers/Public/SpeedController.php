<?php

namespace App\Http\Controllers\Public;

use App\Enums\FilmCinemaStatus;
use App\Http\Controllers\Controller;
use App\Models\FilmVideoVariant;
use Illuminate\Database\Eloquent\Builder;

class SpeedController extends Controller
{
    public function index()
    {
        return response()->json([
            'stream' => FilmVideoVariant::where('height', 1080)
                                        ->whereHas('film', fn(Builder $has) => $has
                                            ->where('cinema_status', FilmCinemaStatus::Published)
                                        )
                                        ->first()->path
        ]);
    }
}

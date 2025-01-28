<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Resources\Management\RatingResource;
use App\Models\Film;
use App\Models\Rating;
use Illuminate\Http\Request;

class  RatingController extends Controller
{
    public function index(Request $request, Film $film)
    {
        return RatingResource::collection(
            $film->ratings()
                 ->where('user_id', $request->user()->id)
                 ->latest()
                 ->get()
        );
    }
}

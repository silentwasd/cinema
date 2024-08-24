<?php

namespace App\Http\Controllers;

use App\Http\Resources\RatingResource;
use App\Models\Film;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function index(Film $film)
    {
        return RatingResource::collection(
            $film->ratings()
                 ->latest()
                 ->get()
        );
    }

    public function store(Request $request, Film $film)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'data'    => 'required|array'
        ]);

        $film->ratings()->create($data);
    }

    public function update(Request $request, Film $film, Rating $rating)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'data'    => 'required|array'
        ]);

        $rating->update($data);
    }

    public function destroy(Film $film, Rating $rating)
    {
        $rating->delete();
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Resources\RatingResource;
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

    public function store(Request $request, Film $film)
    {
        $data = $request->validate([
            'data' => 'required|array'
        ]);

        $film->ratings()->create([
            ...$data,
            'user_id' => $request->user()->id
        ]);
    }

    public function update(Request $request, Film $film, Rating $rating)
    {
        $data = $request->validate([
            'data' => 'required|array'
        ]);

        $rating->update([
            ...$data,
            'user_id' => $request->user()->id
        ]);
    }

    public function destroy(Film $film, Rating $rating)
    {
        $rating->delete();
    }
}

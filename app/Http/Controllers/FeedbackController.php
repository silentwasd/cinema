<?php

namespace App\Http\Controllers;

use App\Http\Resources\FeedbackResource;
use App\Models\Feedback;
use App\Models\Film;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Film $film)
    {
        return FeedbackResource::collection(
            $film->feedbacks()
                 ->with('user')
                 ->latest()
                 ->get()
        );
    }

    public function store(Request $request, Film $film)
    {
        if ($request->user()->cannot('create', [Feedback::class, $film]))
            abort(403);

        $data = $request->validate([
            'text'     => 'nullable|string|max:512',
            'reaction' => 'nullable|integer|min:-1|max:1'
        ]);

        if (!($data['text'] ?? false) && ($data['reaction'] ?? 0) == 0)
            abort(400, 'Укажите реакцию или напишите отзыв.');

        $user = $request->user();

        $user->feedbacks()->create([
            ...$data,
            'film_id' => $film->id
        ]);
    }

    public function update(Request $request, Film $film, Feedback $feedback)
    {
        if ($request->user()->cannot('update', $feedback))
            abort(403);

        $data = $request->validate([
            'text'     => 'nullable|string|max:512',
            'reaction' => 'nullable|integer|min:-1|max:1'
        ]);

        if (!($data['text'] ?? false) && ($data['reaction'] ?? 0) == 0)
            abort(400, 'Укажите реакцию или напишите отзыв.');

        $feedback->update($data);
    }
}

<?php

namespace App\Policies;

use App\Models\Feedback;
use App\Models\Film;
use App\Models\User;

class FeedbackPolicy
{
    public function create(User $user, Film $film): bool
    {
        return !$user->feedbacks()->where('film_id', $film->id)->exists();
    }

    public function update(User $user, Feedback $feedback): bool
    {
        return $feedback->user_id == $user->id;
    }
}

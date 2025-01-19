<?php

namespace App\Console\Commands;

use App\Models\Feedback;
use App\Models\Rating;
use Illuminate\Console\Command;

class ConvertRatingToFeedbackCommand extends Command
{
    protected $signature = 'convert:rating-to-feedback';

    protected $description = 'Convert rating to feedback';

    public function handle(): void
    {
        foreach (Rating::all() as $rating) {
            $feedback = Feedback::create([
                'film_id' => $rating->film_id,
                'user_id' => $rating->user_id,
                'text'    => $rating->data['comment']
            ]);

            $feedback->created_at = $rating->created_at;
            $feedback->save();
        }
    }
}

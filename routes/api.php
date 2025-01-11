<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Cinema;
use App\Http\Controllers\Management;
use App\Http\Controllers\Production;
use App\Http\Controllers\Public;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::prefix('management')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiSingleton('profile', Management\ProfileController::class)->only(['show']);
        Route::apiResource('films', Management\FilmController::class)->except(['show']);
        Route::apiResource('films.ratings', Management\RatingController::class)->except(['show']);
        Route::apiResource('films.persons', Management\FilmPersonController::class)->except(['show']);
        Route::apiResource('film-watchers', Management\FilmWatcherController::class)->except(['show']);
        Route::apiResource('people', Management\PersonController::class)->except(['show']);
    });

    Route::apiResource('films', Management\FilmController::class)->only(['show']);
});

Route::middleware(['auth:sanctum', AdminMiddleware::class])->prefix('production')->group(function () {
    Route::apiResource('downloads', Production\DownloadController::class);

    Route::apiResource('films', Production\FilmController::class);
    Route::get('films/{film}/watch', [Production\FilmController::class, 'watch']);

    Route::apiResource('films.video-variants', Production\FilmVideoVariantController::class);
    Route::post('films/{film}/video-variants/preview', [Production\FilmVideoVariantController::class, 'preview']);

    Route::apiResource('films.audio-variants', Production\FilmAudioVariantController::class);
    Route::post('films/{film}/audio-variants/preview', [Production\FilmAudioVariantController::class, 'preview']);
    Route::post('films/{film}/audio-variants/{audio_variant}/mark-as-default', [Production\FilmAudioVariantController::class, 'markAsDefault']);
});

Route::middleware('auth:sanctum')->prefix('cinema')->group(function () {
    /** Until multi-user sync player not exists... */
    //Route::apiResource('films', Cinema\FilmController::class)->only(['show']);
    //Route::get('films/{film}/watch', [Cinema\FilmController::class, 'watch']);
});

Route::apiResource('cinema/films', Cinema\FilmController::class)->only(['show']);
Route::get('cinema/films/{film}/watch', [Cinema\FilmController::class, 'watch']);

Route::apiResource('films', Public\FilmController::class)->only(['index']);

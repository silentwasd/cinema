<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Cinema;
use App\Http\Controllers\FeedbackController;
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
        Route::get('film-watchers/by-film/{film}', [Management\FilmWatcherController::class, 'byFilm']);
        Route::apiResource('people', Management\PersonController::class)->except(['show']);
        Route::apiResource('companies', Management\CompanyController::class)->except(['show']);

        Route::get('genres', [Management\GenreController::class, 'index']);
        Route::get('countries', [Management\CountryController::class, 'index']);
        Route::get('tags', [Management\TagController::class, 'index']);
    });

    Route::apiResource('films', Management\FilmController::class)->only(['show']);
    Route::apiResource('people', Management\PersonController::class)->only(['show']);
    Route::apiResource('companies', Management\CompanyController::class)->only(['show']);
});

Route::middleware(['auth:sanctum', AdminMiddleware::class])->prefix('production')->group(function () {
    Route::apiResource('downloads', Production\DownloadController::class)->except(['show']);
    Route::post('downloads/{download}/stop', [Production\DownloadController::class, 'stop']);
    Route::post('downloads/{download}/start', [Production\DownloadController::class, 'start']);

    Route::apiResource('films', Production\FilmController::class);
    Route::get('films/{film}/watch', [Production\FilmController::class, 'watch']);

    Route::apiResource('films.video-variants', Production\FilmVideoVariantController::class);
    Route::post('films/{film}/video-variants/preview', [Production\FilmVideoVariantController::class, 'preview']);

    Route::apiResource('films.audio-variants', Production\FilmAudioVariantController::class);
    Route::post('films/{film}/audio-variants/preview', [Production\FilmAudioVariantController::class, 'preview']);
    Route::post('films/{film}/audio-variants/{audio_variant}/mark-as-default', [Production\FilmAudioVariantController::class, 'markAsDefault']);
});

Route::apiResource('cinema/films', Cinema\FilmController::class)->only(['show']);
Route::get('cinema/films/{film}/watch', [Cinema\FilmController::class, 'watch']);

Route::apiResource('films', Public\FilmController::class)->only(['index']);
Route::get('sitemap', [Public\SitemapController::class, 'index']);
Route::get('speed', [Public\SpeedController::class, 'index']);

Route::prefix('films/{film}/feedback')->group(function () {
    Route::get('', [FeedbackController::class, 'index']);
    Route::post('', [FeedbackController::class, 'store'])->middleware('auth:sanctum');
    Route::put('{feedback}', [FeedbackController::class, 'update'])->middleware('auth:sanctum');
});

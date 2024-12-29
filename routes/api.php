<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\FilmWatcherController;
use App\Http\Controllers\RatingController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('films', FilmController::class);
    Route::delete('films', [FilmController::class, 'destroyMany']);

    Route::apiResource('films/{film}/ratings', RatingController::class);

    Route::apiResource('film-watchers', FilmWatcherController::class);
});

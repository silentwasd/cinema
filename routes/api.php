<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Management;
use App\Http\Controllers\Public;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->prefix('management')->group(function () {
    Route::apiResource('films', Management\FilmController::class)->except(['show']);
    Route::apiResource('films.ratings', Management\RatingController::class)->except(['show']);
    Route::apiResource('film-watchers', Management\FilmWatcherController::class)->except(['show']);
});

Route::apiResource('films', Public\FilmController::class)->only(['index']);

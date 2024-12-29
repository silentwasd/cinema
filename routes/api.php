<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\FilmWatcherController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('films', FilmController::class);
    Route::delete('films', [FilmController::class, 'destroyMany']);
    Route::patch('films/update-list/{list}', [FilmController::class, 'updateListMany']);
    Route::get('films/{film}/list/{list}/users', [FilmController::class, 'listUsers']);
    Route::patch('films/{film}/list/{list}/users', [FilmController::class, 'updateList']);

    Route::apiResource('films/{film}/ratings', RatingController::class);

    Route::apiResource('film-watchers', FilmWatcherController::class);

    Route::apiResource('lists', ListController::class);
    Route::delete('lists', [ListController::class, 'destroyMany']);

    Route::apiResource('users', UserController::class);
    Route::delete('users', [UserController::class, 'destroyMany']);
});

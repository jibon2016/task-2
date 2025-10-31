<?php

use App\Http\Controllers\Auth\AuthApiController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/regstration', [AuthApiController::class, 'registration']);

Route::group(['middleware' => 'auth:sanctum'], function () {
   Route::post('/logout', [AuthApiController::class, 'logout']);

   Route::get('/events', [EventController::class, 'events']);
   Route::get('/event/{id}', [EventController::class, 'event']);

   Route::group(['middleware' => ['role:organizer']], function () {
        Route::post('/events', [EventController::class, 'create']);
        Route::put('/events/{id}', [EventController::class, 'update']);
   });
});

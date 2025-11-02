<?php

use App\Http\Controllers\Auth\AuthApiController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TicketController;
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
        Route::delete('/events/{id}', [EventController::class, 'delete']);
        Route::post('/events/{id}/tickets', [TicketController::class, 'addTickets']);
        Route::put('/tickets/{id}', [TicketController::class, 'updateTicket']);
       Route::delete('/tickets/{id}', [TicketController::class, 'deleteTicket']);
   });

   Route::group(['middleware' => ['role:customer']], function () {
       Route::post('/tickets/{id}/bookings', [BookingController::class, 'store'])->middleware('prevent_double_booking');
       Route::get('/bookings', [BookingController::class, 'userBookings']);
       Route::put('/bookings/{id}/cancel', [BookingController::class, 'updateBooking']);
   });

   Route::post('/bookings/{id}/payment', [PaymentController::class, 'create']);
   Route::get('/payments/{id}', [PaymentController::class, 'paymentDetails']);
});

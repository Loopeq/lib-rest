<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\UserController;
use App\Http\Middlewares\AdminMiddleware;

Route::post('/books/{id}/reserve', [BookController::class, 'reserve']);
Route::post('/books/{id}/return', [BookController::class, 'return']);
Route::post('/user/register', [UserController::class, 'register']);

Route::middleware([AdminMiddleware::class])->group(function (){
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/search', [BookController::class, 'search']);
    Route::post('/books', [BookController::class, 'store']);
    Route::get('/books/{id}', [BookController::class, 'show']);
    Route::put('/books/{id}', [BookController::class, 'update']);
    Route::delete('/books/{id}', [BookController::class, 'destroy']);
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::get('/reservations/{id}', [ReservationController::class, 'show']);
    Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);
});
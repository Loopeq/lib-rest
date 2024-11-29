<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\BookController;
use App\Http\Middleware\AdminMiddleware;

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

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SquarePaymentController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/customer/create', [SquarePaymentController::class, 'create']);
Route::post('/square/customer/store', [SquarePaymentController::class , 'store'])->name('square.customer.store');

//https://8d03-39-45-98-253.ngrok-free.app/square/callback
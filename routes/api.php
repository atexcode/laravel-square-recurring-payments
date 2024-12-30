<?php

use App\Http\Controllers\SquarePaymentController;
use Illuminate\Support\Facades\Route;

Route::post('/square/callback', [SquarePaymentController::class, 'handleCallback'])->name('square.callback');

Route::get('/up', function () {
    return response()->json(['status' => 'ok']);
});


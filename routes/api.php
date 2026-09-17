<?php

use App\Http\Controllers\ItemRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Route::get('/items/requests/models/input', [ItemRequestController::class, 'dailyRequest']);
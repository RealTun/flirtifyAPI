<?php

use App\Http\Controllers\Api\InterestTypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');      

Route::get('interest-type', [InterestTypeController::class, 'index']);
<?php

use App\Http\Controllers\Api\InterestTypeController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\RelationshipTypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');      

// interest
Route::get('interest-type', [InterestTypeController::class, 'index']);

// relationship
Route::get('relationship-type', [RelationshipTypeController::class, 'getRelationships']);
Route::get('relationship-type/user/{id}', [RelationshipTypeController::class, 'getRelationshipByUser'])->name('getRelationshipUser');
Route::post('relationship-type/user', [RelationshipTypeController::class, 'storeUserRelationship']);

// connect
Route::get('users-connect/{id}', [MatchController::class, 'getUserToConnect']);

// matcher
Route::get('matchers/{id}', [MatchController::class, 'getMatchersByUser']);
Route::post('matchers', [MatchController::class, 'storeUserLike']);
<?php

use App\Http\Controllers\Api\InterestTypeController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\RelationshipTypeController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PhotoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// authen
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// protect route
Route::middleware(['auth.api', 'check.token.expiration'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user'])->name('getUser');

    // interest
    Route::get('interest-type', [InterestTypeController::class, 'index']);
    Route::get('interest-type/{id}', [InterestTypeController::class, 'getInterestByUser'])->name('getInterestUser');
    Route::post('interest-type', [InterestTypeController::class, 'storeInterestUser']);
    Route::delete('interest-type/{id}', [InterestTypeController::class, 'deleteUserInterest']);

    // relationship
    Route::get('relationship-type', [RelationshipTypeController::class, 'getRelationships']);
    Route::get('relationship-type/user/{id}', [RelationshipTypeController::class, 'getRelationshipByUser'])->name('getRelationshipUser');
    Route::post('relationship-type/user', [RelationshipTypeController::class, 'storeUserRelationship']);
    Route::delete('relationship-type/user/{id}', [RelationshipTypeController::class, 'deleteUserRelationship']);

    // connect
    Route::get('users-connect/{id}', [MatchController::class, 'getUserToConnect']);

    // matcher
    Route::get('matchers/{id}', [MatchController::class, 'getMatchersByUser']);
    Route::post('matchers', [MatchController::class, 'storeUserLike']);

    // photo
    Route::get('user-photos/{id}', [PhotoController::class,'getUserPhotos']);
    Route::post('user-photos/upload', [PhotoController::class,'storeUserPhotos']);
});

// interest
// Route::get('interest-type', [InterestTypeController::class, 'index']);

// relationship
// Route::get('relationship-type', [RelationshipTypeController::class, 'getRelationships']);
// Route::get('relationship-type/user/{id}', [RelationshipTypeController::class, 'getRelationshipByUser'])->name('getRelationshipUser');
// Route::post('relationship-type/user', [RelationshipTypeController::class, 'storeUserRelationship']);

// // connect
// Route::get('users-connect/{id}', [MatchController::class, 'getUserToConnect']);

// matcher
// Route::get('matchers/{id}', [MatchController::class, 'getMatchersByUser']);
// Route::post('matchers', [MatchController::class, 'storeUserLike']);
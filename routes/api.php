<?php

use App\Events\MessageSent;
use App\Http\Controllers\Api\InterestTypeController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\RelationshipTypeController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PhotoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// authen
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::get('interest-type', [InterestTypeController::class, 'index']);
Route::get('relationship-type', [RelationshipTypeController::class, 'getRelationships']);

// protect route
Route::middleware(['auth.api'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user'])->name('getUser');

    // interest
    Route::get('interest-type/user', [InterestTypeController::class, 'getInterestByUser'])->name('getInterestUser');
    Route::post('interest-type/user', [InterestTypeController::class, 'storeInterestUser']);
    Route::delete('interest-type/user/{id}', [InterestTypeController::class, 'deleteUserInterest']);

    // relationship
    Route::get('relationship-type/user', [RelationshipTypeController::class, 'getRelationshipByUser'])->name('getRelationshipUser');
    Route::post('relationship-type/user', [RelationshipTypeController::class, 'storeUserRelationship']);
    Route::delete('relationship-type/user/{id}', [RelationshipTypeController::class, 'deleteUserRelationship']);

    // connect
    Route::get('users-connect', [MatchController::class, 'getUserToConnect']);

    // matcher
    Route::get('matchers', [MatchController::class, 'getMatchersByUser']);
    Route::post('matchers', [MatchController::class, 'storeUserLike']);

    // photo
    Route::get('user-photos', [PhotoController::class,'getUserPhotos']);
    Route::post('user-photos/upload', [PhotoController::class,'storeUserPhotos']);

    // chat
    // Route::get('/chat', [MessageController::class, 'index']);
    Route::get('/chat/messages/{receiver_id}', [MessageController::class, 'getMessages']);
    Route::post('/chat/send', [MessageController::class, 'store']);
});

// Route::get('/chat', [MessageController::class, 'show'])->name('chat.show');
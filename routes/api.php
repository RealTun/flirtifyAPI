<?php

use App\Http\Controllers\Api\InterestTypeController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\RelationshipTypeController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PhotoController;
use App\Http\Controllers\Api\PreferenceController;
use Illuminate\Support\Facades\Route;


// authen
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::get('interest-type', [InterestTypeController::class, 'index']);
Route::get('relationship-type', [RelationshipTypeController::class, 'getRelationships']);

// protect route
Route::middleware(['auth.api'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'getUser'])->name('getUser');
    Route::patch('user', [AuthController::class, 'updateUser']);
    Route::patch('user/lookingfor', [AuthController::class, 'updateLookingFor']);

    // interest
    Route::get('interest-type/user', [InterestTypeController::class, 'getInterestByUser'])->name('getInterestUser');
    Route::post('interest-type/user', [InterestTypeController::class, 'storeInterestUser']);
    Route::delete('interest-type/user/{id}', [InterestTypeController::class, 'deleteUserInterest']);

    // relationship
    Route::patch('relationship-type/user', [RelationshipTypeController::class, 'updateRelationshipType']);

    // connect
    Route::get('users-connect', [MatchController::class, 'getUserToConnect']);

    // matcher
    Route::get('matchers', [MatchController::class, 'getMatchersByUser']);
    Route::post('matchers', [MatchController::class, 'storeUserLike']);

    // photo
    Route::get('user-photos', [PhotoController::class, 'getUserPhotos']);
    Route::post('user-photos/upload', [PhotoController::class, 'storeUserPhotos']);
    Route::delete('user/photos/delete/{photo_id}', [PhotoController::class, 'deleteUserPhotos']);

    // chat
    // Route::get('/chat', [MessageController::class, 'index']);
    Route::get('/chat/messages/{receiver_id}', [MessageController::class, 'getMessages']);
    Route::post('/chat/send', [MessageController::class, 'store']);

    //block
    Route::post('user/block', [MessageController::class, 'blockUser']);
    Route::delete('user/unblock/{id}', [MessageController::class, 'unblockUser']);

    //preference
    Route::post('user/storePreference', [PreferenceController::class, 'storePreference']);
    Route::patch('user/updatePreference', [PreferenceController::class, 'updatePreference']);
});
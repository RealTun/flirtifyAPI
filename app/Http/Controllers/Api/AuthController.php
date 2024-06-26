<?php

namespace App\Http\Controllers\Api;

use App\Models\Preference;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function checkDuplicateEmail(Request $request)
    {
        $rules = [
            'email' => 'unique:user_account,email',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['status' => 'error'], 400);
        }

        return response()->json(['status'=> 'success'], 200);
    }
    
    public function register(Request $request)
    {
        $rules = [
            'email' => 'required|email|unique:user_account,email|max:100',
            'pw' => 'required|string|min:8|max:256',
            'fullname' => 'required|string|max:100',
            'bio' => 'nullable|string',
            'age' => 'integer|min:18',
            'gender' => 'integer|in:0,1,2',
            'looking_for' => 'integer|in:0,1,2',
            'relationship_type' => 'integer|in:1,2,3,4,5,6',
            'location' => 'nullable|string|max:50',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $user = User::create([
            'email' => $request->email,
            'pw' => Hash::make($request->pw),
            'fullname' => $request->fullname,
            'bio' => $request->bio,
            'age' => $request->age,
            'gender' => $request->gender,
            'looking_for' => $request->looking_for,
            'relationship_type' => $request->relationship_type,
            'location' => $request->location
        ]);

        // return response()->json($user, 201);
        $token = $user->createToken('auth_token')->plainTextToken;
        $expiration = Carbon::now()->addDays(7);
        $user->tokens()->where('tokenable_id', $user->id)->update(['expires_at' => $expiration]);

        Preference::create([
            'user_account_id' => $user->id,
            'min_age' => 18,
            'max_age' => 25,
            'max_distance' => 15
        ]);

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->pw)) {
            $token = $user->createToken('auth_token')->plainTextToken;
            $expiration = Carbon::now()->addDays(7);
            $user->tokens()->where('tokenable_id', $user->id)->update(['expires_at' => $expiration]);

            return response()->json([
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 200);
        }

        return response()->json(['message' => 'Unauthorized'], 401);
    }

    public function logout(Request $request)
    {
        $user = $request->user('sanctum');
        if ($user) {
            $user->currentAccessToken()->delete();
            return response()->json(['message' => 'Logged out successfully'], 200);
        }

        return response()->json(['message' => 'No authenticated user found.'], 401);
    }

    public function getUser()
    {
        $user = auth('sanctum')->user();
        $photos = [];
        $interests = [];

        foreach ($user->photos as $item) {
            array_push($photos, $item->imageUrl());
        }

        foreach ($user->interests as $item) {
            array_push($interests, $item->interestType->name_interest_type);
        }

        $data = [
            "id" => $user->id,
            "fullname" => $user->fullname,
            'bio' => $user->bio,
            'age' => $user->age,
            'gender' => $user->gender,
            'looking_for' => $user->looking_for,
            'location' => $user->location,
            "interests" => $interests,
            "relationship" => $user->relationshipType->name_relationship,
            "photos" => $photos != null ? $photos : ["https://placebeard.it/500/500", "https://placebeard.it/500/500", "https://placebeard.it/500/500", "https://placebeard.it/500/500"],
            "max_distance" => $user->preference->max_distance,
            "min_age" => $user->preference->min_age,
            "max_age" => $user->preference->max_age
        ];
        return response()->json($data, 200);
    }

    public function updateUser(Request $request)
    {
        $rules = [
            'fullname' => 'required|string|max:100',
            'bio' => 'nullable|string',
            'gender' => 'integer|in:0,1,2',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }

        $user = $request->user('sanctum');
        if($user->update($request->all())) {
            return response()->json($user, 200);
        }
        return response()->json(["status" => "update user error"], 400);
    }

    public function updateLookingFor(Request $request)
    {
        $rules = [
            'looking_for' => 'integer|in:0,1,2'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['message'=> $validator->errors()], 400);
        }

        $user = $request->user('sanctum');
        $user->looking_for = $request->looking_for;
        $user->save();
        return response()->json(["status" => "update looking_for successfully"], 200);
    }

    public function updateGender(Request $request)
    {
        $rules = [
            'gender' => 'integer|in:0,1,2'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['message'=> $validator->errors()], 400);
        }

        $user = $request->user('sanctum');
        $user->gender = $request->gender;
        $user->save();
        return response()->json(["status" => "update gender successfully"], 200);
    }

    public function updateLocation(Request $request)
    {
        $rules = [
            'location' => 'required|string'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }

        $user = $request->user('sanctum');
        $user->location = $request->location;
        $user->save();
        return response()->json(["status" => "update location successfully"], 200);
    }

    public function updatePassword(Request $request)
    {
        $rules = [
            'email' => 'required',
            'pw' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->pw = Hash::make($request->pw);
        $user->save();
        return response()->json(["status" => "update password successfully"], 200);
    }
}

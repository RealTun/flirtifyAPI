<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\UserPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $rules = [
            'email' => 'required|email|unique:user_account,email|max:100',
            'phone' => 'required|unique:user_account,phone|max:20',
            'pw' => 'required|string|min:8|max:256',
            'fullname' => 'required|string|max:100',
            'bio' => 'nullable|string',
            'age' => 'nullable|integer|min:18',
            'gender' => 'nullable|integer|in:0,1,2',
            'looking_for' => 'nullable|integer|in:0,1,2',
            'location' => 'nullable|string|max:50',
            'confirmation_code' => 'required|string|size:6',
            'confirmation_time' => 'nullable|date'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }

        $user = User::create([
            'email' => $request->email,
            'phone' => $request->phone,
            'pw' => Hash::make($request->pw),
            'fullname' => $request->fullname,
            'bio' => $request->bio,
            'age' => $request->age,
            'gender' => $request->gender,
            'looking_for' => $request->looking_for,
            'location' => $request->location,
            'confirmation_code' => $request->confirmation_code,
            'confirmation_time' => $request->confirmation_time
        ]);

        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->pw)) {
            $token = $user->createToken('auth_token')->plainTextToken;
            $expiration = Carbon::now()->addDays(15);
            $user->tokens()->where('tokenable_id', $user->id)->update(['expires_at' => $expiration]);
            // return response()->json(['token' => $token, 'user' => $user]);
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

    public function user(Request $request)
    {
        $user = $request->user('sanctum');
        $photos = [];
        $interests = [];
        $relationships = [];

        foreach ($user->photos as $item) {
            array_push($photos, $item->link);
        }

        foreach ($user->interests as $item) {
            array_push($interests, $item->interestType->name_interest_type);
        }

        foreach ($user->relationships as $item) {
            array_push($relationships, $item->relationshipType->name_relationship);
        }

        $data = [];
        array_push($data, [
            "id" => $user->id,
            "fullname" => $user->fullname,
            'bio' => $user->bio,
            'age' => $user->age,
            'locking_for' => $user->looking_for,
            'location' => $user->location,
            "interests" => $interests,
            "relationships"=> $relationships,
            "photos" => $photos
        ]);
        return response()->json($data, 200);
    }
}

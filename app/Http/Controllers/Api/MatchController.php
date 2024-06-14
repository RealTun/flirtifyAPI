<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserConnection;
use App\Models\UserPhoto;

class MatchController extends Controller
{
    //
    public function getUserToConnect()
    {
        $id = auth('sanctum')->user()->id;
        $users_id = User::whereRaw('gender = (SELECT looking_for FROM user_account WHERE id = ?)', [$id])
            ->whereRaw('looking_for = (SELECT gender FROM user_account WHERE id = ?)', [$id])
            ->whereNotIn('id', function ($query) use ($id) {
                $query->select('user2_id')
                    ->from('user_connection')
                    ->where('user1_id', '=', $id);
            })
            ->pluck('id');

        $data = [];
        foreach ($users_id as $user_id) {
            $user = User::find($user_id);
            $photos = [];
            $interests = [];
            $relationships = [];

            foreach ($user->photos as $item) {
                array_push($photos, $item->imageUrl());
            }

            foreach ($user->interests as $item) {
                array_push($interests, $item->interestType->name_interest_type);
            }

            foreach ($user->relationships as $item) {
                array_push($relationships, $item->relationshipType->name_relationship);
            }

            array_push($data, [
                "id" => $user->id,
                "fullname" => $user->fullname,
                'bio' => $user->bio,
                'age' => $user->age,
                'locking_for' => $user->looking_for,
                'location' => $user->location,
                "interests" => $interests,
                "relationships" => $relationships,
                "photos" => $photos
            ]);
        }

        return response()->json($data, 200);
    }

    public function getMatchersByUser()
    {
        $id = auth('sanctum')->user()->id;
        // collection user2_id by user1_id
        $matchers_id = UserConnection::whereNotNull('match_date')
            ->where("user1_id", $id)
            ->pluck("user2_id");

        $matchers = [];
        foreach ($matchers_id as $matcher_id) {
            $user = User::select('id', 'fullname')->find($matcher_id);
            $photo = UserPhoto::where('user_account_id', $matcher_id)->first();
            $match = UserConnection::where('user1_id', $id)
                ->where('user2_id', $matcher_id)->first();
            // $message = Message::where('match_id', $match->id)->orderBy('time_sent', 'desc')->first();
            $message = Message::where(function ($query) use ($id, $matcher_id) {
                $query->where('sender_id', $id)
                    ->where('receiver_id', $matcher_id);
                })
                ->orWhere(function ($query) use ($id, $matcher_id) {
                    $query->where('receiver_id', $id)
                        ->where('sender_id', $matcher_id);
                })
                ->orderBy('time_sent', 'desc')
                ->first();

            array_push($matchers, [
                "matcher_id" => $user->id,
                "match_id" => $match->id,
                "fullname" => $user->fullname,
                "imageUrl" => $photo->imageUrl(),
                "last_message" => $message->message_content
            ]);
        }

        return response()->json($matchers, 201);
    }

    public function storeUserLike(Request $request)
    {
        $rules = [
            'user2_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => "user2_id cannot be left blank!"
            ], 400);
        }

        $user1_id = $request->user('sanctum')->id;
        $user2_id = $request->user2_id;
        $isExisted = UserConnection::where([
            'user1_id' => $user1_id,
            'user2_id' => $user2_id
        ])->exists();
        if (!User::find($user2_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot find this user!'
            ], 400);
        } else if ($user1_id == $user2_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot like yourself!'
            ], 400);
        } else if ($isExisted) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have liked this person before!'
            ], 400);
        }

        UserConnection::create([
            'user1_id' => $user1_id,
            'user2_id' => $user2_id
        ]);
        // check 2 user is matched
        $isMatched = UserConnection::where('user1_id', $user2_id)
            ->where('user2_id', $user1_id)
            ->exists();
        if ($isMatched) {
            $currentTime = date('Y-m-d H:i:s');
            // update column match_date in user_connection, mark it is matched
            UserConnection::where('user1_id', $user1_id)
                ->where('user2_id', $user2_id)
                ->update([
                    'match_date' => $currentTime
                ]);
            ////
            UserConnection::where('user1_id', $user2_id)
                ->where('user2_id', $user1_id)
                ->update([
                    'match_date' => $currentTime
                ]);
            // response
            return response()->json([
                'status' => "success",
                'message' => "You matched!"
            ], 200);
        }

        return response()->json([
            'status' => "success",
            'message' => "Wating they like you!"
        ], 201);
    }
}

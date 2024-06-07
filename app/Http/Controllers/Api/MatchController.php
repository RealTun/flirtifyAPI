<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserPhoto;

class MatchController extends Controller
{
    //
    public function getUserToConnect()
    {
        $id = auth('sanctum')->user()->id;
        $users_id = DB::table('user_account')
            ->whereRaw('gender = (SELECT looking_for FROM user_account WHERE id = ?)', [$id])
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
            $photos = UserPhoto::where('user_account_id', $user_id)->select('link')->get();
            $interests = $user->interests;
            $relationships = $user->relationships;
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
        }

        return response()->json([
            'message' => 'success',
            'users' => $data
        ], 200);
    }

    public function getMatchersByUser()
    {
        $id = auth('sanctum')->user()->id;
        // collection user2_id by user1_id
        $matchers_id = DB::table('user_connection')
            ->whereNotNull('match_date')
            ->where("user1_id", $id)
            ->pluck("user2_id");

        $matchers = [];
        foreach ($matchers_id as $matcher_id) {
            $user = User::select('id', 'fullname')->find($matcher_id);
            $photo = UserPhoto::where('user_account_id', $matcher_id)->select('link')->first();

            array_push($matchers, [
                [
                    "id" => $user->id,
                    "fullname" => $user->fullname,
                    "imageUrl" => $photo->link
                ]
            ]);
        }

        return response()->json([
            'status' => 'success',
            'matchers' => $matchers
        ], 201);
    }

    public function storeUserLike(Request $request)
    {
        $rules = [
            // 'user1_id' => 'required',
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
        $isExisted = DB::table("user_connection")->where([
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

        DB::table("user_connection")->insert([
            'user1_id' => $user1_id,
            'user2_id' => $user2_id
        ]);
        // check 2 user is matched
        $isMatched = DB::table('user_connection')
            ->where('user1_id', $user2_id)
            ->where('user2_id', $user1_id)
            ->exists();
        if ($isMatched) {
            $currentTime = date('Y-m-d H:i:s');
            // update column match_date in user_connection, mark it is matched
            DB::table('user_connection')
                ->where('user1_id', $user1_id)
                ->where('user2_id', $user2_id)
                ->update([
                    'match_date' => $currentTime
                ]);
            ////
            DB::table('user_connection')
                ->where('user1_id', $user2_id)
                ->where('user2_id', $user1_id)
                ->update([
                    'match_date' => $currentTime
                ]);
            // response
            return response()->json([
                'status' => "success",
                // 'user1' => $user1_id,
                // 'user2' => $user2_id,
                'message' => "You matched!"
            ], 200);
        }

        return response()->json([
            'status' => "success",
            'message' => "Wating they like you!"
        ], 201);
    }
}

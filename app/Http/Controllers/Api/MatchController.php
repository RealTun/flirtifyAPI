<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatchController extends Controller
{
    //
    public function getUserToConnect(int $id)
    {
        $users = DB::table('user_account')
            ->whereRaw('gender = (SELECT looking_for FROM user_account WHERE id = ?)', [$id])
            ->whereRaw('looking_for = (SELECT gender FROM user_account WHERE id = ?)', [$id])
            ->whereNotIn('id', function ($query) use ($id) {
                $query->select('user2_id')
                    ->from('user_connection')
                    ->where('user1_id', '=', $id);
            })
            ->get();

        return response()->json($users, 201);
    }

    public function getMatchersByUser(int $id)
    {
        // collection user2_id by user1_id
        $user2_id = DB::table('user_connection')
            ->whereNotNull('match_date')
            ->where("user1_id", $id)
            ->pluck("user2_id");

        $matchers = DB::table('user_account')
            ->whereIn('id', $user2_id)
            ->get();

        return response()->json($matchers, 201);
    }

    public function storeUserLike(Request $request)
    {
        $rules = [
            'user1_id' => 'required',
            'user2_id' => 'required',
        ];

        if ($request->validate($rules)) {
            $user1_id = $request->user1_id;
            $user2_id = $request->user2_id;

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
                    'response' => "success",
                    'user1' => $user1_id,
                    'user2' => $user2_id,
                    'message' => "user1 matched to user2"
                ], 200);
            }

            return response()->json([
                'response' => "success",
                'message' => "Wating user2 like user1"
            ], 201);
        }

        return response()->json([
            'response' => 'error',
            'message' => "user1_id and user2_id cannot be left blank!"
        ], 400);
    }
}

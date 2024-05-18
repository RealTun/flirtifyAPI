<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatchController extends Controller
{
    //
    public function getAllMatcher(){
        $matchers = DB::table('user_connection')
                    ->join('user_account', 'user_connection.user1_id', 'user_account.id')
                    ->whereNotNull('user_connection.match_date')
                    ->get();
        return response()->json($matchers, 201);
    }

    public function getMatchersByUser(string $id){
        $user2_id = DB::table('user_connection')
                    ->whereNotNull('match_date')
                    ->where("user1_id", $id)
                    ->pluck("user2_id");

        $matchers = DB::table('user_account')
                    ->whereIn('id', $user2_id)
                    ->get();

        return response()->json($matchers, 201);
    }
}

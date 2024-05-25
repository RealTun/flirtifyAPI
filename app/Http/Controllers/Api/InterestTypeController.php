<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InterestType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InterestTypeController extends Controller
{
    //
    public function index(){
        $interests = DB::table('interest_type')->get();
        return response()->json($interests, 201);
    }

    public function getInterestByUser(int $id){
        if ($id != auth('sanctum')->user()->id) {
            return response()->json(['message' => 'Not allowed'], 403);
        }
        $data = DB::table('interest_user')
            ->join('user_account', 'user_account_id', 'user_account.id')
            ->join('interest_type', 'interest_type_id', 'interest_type.id')
            ->where('user_account.id', $id)
            ->pluck('interest_type.name_interest_type');
        if(count($data) < 1){
            return response()->json([
                'message' => "This user has not established a interest"
            ], 400);
        }
        return response()->json([
            'status' => 'success',
            'user_name' => auth('sanctum')->user()->fullname,
            'name_interest' => $data
        ], 200);
    }

    public function storeInterestUser(Request $request){
        $rules = [
            'interest_type_id' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => "interest_type_id cannot be left blank!"
            ], 400);
        }

        $user_id = $request->user('sanctum')->id;
        $interest_type_id = $request->interest_type_id;
        $isExisted = DB::table('interest_user')->where([
            'user_account_id' => $user_id,
            'interest_type_id' => $interest_type_id
        ])->exists();
        //
        if ($isExisted) {
            return response()->json([
                'status' => 'error',
                'message' => "This user has this interest!"
            ], 400);
        }

        if(!InterestType::find($interest_type_id)){
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot find this interest!',
            ], 400);
        }
        DB::table('interest_user')->insert([
            'user_account_id' => $user_id,
            'interest_type_id' => $interest_type_id
        ]);
        return redirect()->route('getInterestUser', ['id' => $user_id]);
    }

    public function deleteUserInterest(int $interest_type_id){
        $data = [
            'user_account_id' => auth('sanctum')->user()->id,
            'interest_type_id' => $interest_type_id
        ];
        $isExisted = DB::table('interest_user')->where($data)->exists();
        if(!$isExisted){
            return response()->json([
                'status' => 'error',
                'message' => "The interest of user you're trying to access could not be found!"
            ], 400);
        }

        DB::table('interest_user')->where($data)->delete();
        return response()->json([
            'status' => 'success',
            'message' => "The user's interest has been successfully deleted!"
        ], 200);
    }
}

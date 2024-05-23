<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RelationshipTypeController extends Controller
{
    //
    public function getRelationships()
    {
        $relationships = DB::table('relationship_type')->get();
        return response()->json($relationships, 201);
    }

    public function getRelationshipByUser(int $id)
    {
        $data = DB::table('interested_in_relation')
            ->join('user_account', 'user_account_id', 'user_account.id')
            ->join('relationship_type', 'relationship_type_id', 'relationship_type.id')
            ->where('user_account.id', $id)
            ->pluck('relationship_type.name_relationship');
        return response()->json([
            'status' => 'success',
            'user_id' => $id,
            'name_relationship' => $data
        ], 302);
    }

    public function storeUserRelationship(Request $request)
    {
        $rules = [
            'user_id' => 'required',
            'relationship_type_id' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => "user_id and relationship_type_id cannot be left blank!"
            ], 400);
        }

        $user_id = $request->user_id;
        $relationship_type_id = $request->relationship_type_id;
        $isExisted = DB::table('interested_in_relation')->where([
            'user_account_id' => $user_id,
            'relationship_type_id' => $relationship_type_id
        ])->exists();
        //
        if ($isExisted) {
            return response()->json([
                'status' => 'error',
                'message' => "This user has this relationship!"
            ], 400);
        }

        DB::table('interested_in_relation')->insert([
            'user_account_id' => $user_id,
            'relationship_type_id' => $relationship_type_id
        ]);
        return redirect()->route('getRelationshipUser', ['id' => $user_id]);
    }

    public function deleteUserRelationship(int $user_id, int $relationship_type_id)
    {
        $data = [
            'user_account_id' => $user_id,
            'relationship_type_id' => $relationship_type_id
        ];
        $isExisted = DB::table('interested_in_relation')->where($data)->exists();
        if(!$isExisted){
            return response()->json([
                'status' => 'error',
                'message' => "The relationship you're trying to access could not be found!"
            ], 400);
        }

        DB::table('interested_in_relation')->where($data)->delete();
        return response()->json([
            'status' => 'success',
            'message' => "The user's relationship has been successfully deleted!"
        ], 400);
    }
}

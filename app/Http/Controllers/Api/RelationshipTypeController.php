<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RelationshipType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RelationshipTypeController extends Controller
{
    //
    public function getRelationships()
    {
        $relationships = DB::table('relationship_type')->get();
        return response()->json($relationships, 200);
    }

    public function updateRelationshipType(Request $request){
        $rule = [
            'relationsip_type_id' => 'required|integer|in:1,2,3,4,5,6',
        ];

        $validator = Validator::make($request->all(), $rule);
        if ($validator->fails()){
            return response()->json(['status' => 'update relationship error'], 400);
        }

        $user = $request->user('sanctum');
        $user->relationship_type = $request->relationsip_type_id;
        $user->save();
        return response()->json(['status'=> 'success'], 200);
    }
}

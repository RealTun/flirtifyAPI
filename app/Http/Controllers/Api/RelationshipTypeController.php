<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelationshipTypeController extends Controller
{
    //
    public function getRelationships(){
        $relationships = DB::table('relationship_type')->get();
        return response()->json($relationships, 201);
    }
}

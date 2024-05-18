<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InterestTypeController extends Controller
{
    //
    public function index(){
        $interests = DB::table('interest_type')->get();
        return response()->json($interests, 201);
    }
}

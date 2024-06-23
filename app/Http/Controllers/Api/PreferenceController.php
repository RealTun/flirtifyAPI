<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Preference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PreferenceController extends Controller
{
    public function updatePreference(Request $request)
    {
        $rules = [
            'min_age' => 'required|integer|min:18',
            'max_age' => 'required|integer|min:18|max:50',
            'max_distance' => 'required|integer|min:1',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'update preference error',
            ], 400);
        }

        $preference = Preference::where('user_account_id', $request->user('sanctum')->id)->first();
        if ($preference) {
            $preference->min_age = $request->min_age;
            $preference->max_age = $request->max_age;
            $preference->max_distance = $request->max_distance;
            $preference->save();

            return response()->json(["status" => "update preference success"], 200);   
        }

        return response()->json(["status" => "update preference error"], 400);
    }
}

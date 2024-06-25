<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserPhoto;
use Aws\S3\Exception\S3Exception;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PhotoController extends Controller
{
    //
    public function getUserPhotos()
    {
        $id = auth("sanctum")->user()->id;
        $paths = UserPhoto::where('user_account_id', $id)->pluck('link');
        if (count($paths) < 1) {
            return response()->json([
                'status' => 'error',
                'message' => "This user has not set up any photos yet!"
            ], 400);
        }
        // for ($i = 0; $i < count($paths); $i++) {
        //     $paths[$i] = env('CLOUDFLARE_R2_URL') . '/' . $paths[$i];
        // }
        return response()->json([
            'status' => 'success',
            'imgUrl' => $paths,
        ], 200);
    }

    public function storeUserPhotos(Request $request)
    {
        $rules = [
            'url' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }
        
        UserPhoto::create([
            'user_account_id' => $request->user('sanctum')->id,
            'link' => $request->url,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Image uploaded successfully!',
        ], 200);
    }

    // public function deleteUserPhotos(int $photo_id)
    // {
    //     $photo = UserPhoto::find($photo_id);
    //     if (Storage::disk('r2')->exists($photo->link)) {
    //         Storage::disk('r2')->delete($photo->link);
    //         return response()->json(['status' => 'success'], 200);
    //     }
    //     return response()->json(['status' => 'error'], 400);
    // }

    public function deleteUserPhotos(string $url)
    {
        $user = auth("sanctum")->user();
        UserPhoto::where('user_account_id', $user->id)
                ->where('link', $url)
                ->delete();
        
        return response()->json(['status' => 'Delete photo success'], 200);
    }
}

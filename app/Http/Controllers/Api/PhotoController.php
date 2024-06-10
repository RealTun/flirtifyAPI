<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserPhoto;
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
        for ($i = 0; $i < count($paths); $i++){
            $paths[$i] = env('CLOUDFLARE_R2_URL') .'/'. $paths[$i];
        }
        return response()->json([
            'status' => 'success',
            'imgUrl' => $paths,
        ], 200);
    }

    public function storeUserPhotos(Request $request)
    {
        $rules = [
            'image' => 'required|image|mimes:jpeg,png,jpg|max:10240',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => "Cannot upload this photo! Try Again"
            ], 400);
        }
        if ($request->hasFile("image")) {
            $localPath = $request->file("image");
            $imageName = $localPath->getClientOriginalName();
            $folderName = 'photos'.$request->user('sanctum')->id;
            $filePath = $folderName . '/' . $imageName;

            // Check if the folder exists, and create it if it does not
            if (!Storage::disk('r2')->exists($folderName)) {
                Storage::disk('r2')->makeDirectory($folderName);
            }

            // Check if the file already exists in the folder
            if (Storage::disk('r2')->exists($filePath)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File already exists!',
                ], 409); 
            }

            // Store the image in the specified folder on Cloudflare R2
            Storage::disk('r2')->putFileAs($folderName, $localPath, $imageName);

            // Store the image link in the database
            UserPhoto::create([
                'user_account_id' => $request->user('sanctum')->id,
                'link' => Storage::disk('r2')->path($filePath),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Image uploaded successfully!',
            ], 200);
        }
    }
}

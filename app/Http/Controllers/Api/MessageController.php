<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Events\MessageSent;

class MessageController extends Controller
{
    public function show(){
        return view('chat');
    }

    public function index(int $id)
    {
        if ($id != auth('sanctum')->user()->id) {
            return response()->json(['message' => 'Not allowed'], 403);
        }
        $messages = Message::where('sender_id', $id)->orderBy('time_sent')->get()->toArray();
        $data = [];
        foreach ($messages as $message){
            array_push($data, [
                'message' => $message['message_content'],
                'time_sent' => $message['time_sent'],
            ]);  
        }
        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        $rules = [
            'match_id' => 'required|exists:user_connection,id',
            'sender_id' => 'required|exists:user_account,id',
            'receiver_id' => 'required|exists:user_account,id',
            'message_content' => 'required',
        ];
        
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => "Cannot send message! Try again"
            ], 400);
        }

        if ($request->sender_id != auth('sanctum')->user()->id) {
            return response()->json(['message' => 'Not allowed'], 403);
        }

        // $message = Message::create([
        //     'match_id' => $request->match_id,
        //     'sender_id' => $request->sender_id,
        //     'receiver_id' => $request->receiver_id,
        //     'message_content' => $request->message_content,
        // ]);
        
        // Broadcast the message event
        event(new MessageSent($request->user('sanctum'), $request->message_content));
        return response()->json([
            'status' => 'success',
            'message' => $request->message_content
        ], 201);
    }
}

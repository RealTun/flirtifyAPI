<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Events\MessageSent;

class MessageController extends Controller
{
    public function show()
    {
        return view('chat');
    }

    public function index()
    {
        $id = auth('sanctum')->user()->id;
        $messages = Message::where('sender_id', $id)->orderBy('time_sent')->get()->toArray();
        $data = [];
        foreach ($messages as $message) {
            array_push($data, [
                'message' => $message['message_content'],
                'time_sent' => $message['time_sent'],
            ]);
        }
        return response()->json($data, 200);
    }

    public function getSenderMessages(int $receiver_id)
    {
        $id = auth('sanctum')->user()->id;
        $messages = Message::where(function ($query) use ($id, $receiver_id) {
            $query->where('sender_id', $id)
                ->where('receiver_id', $receiver_id);
            })
            // ->orWhere(function ($query) use ($id, $receiver_id) {
            //     $query->where('sender_id', $id)
            //         ->where('receiver_id', $receiver_id);
            // })
            ->orderBy('time_sent', 'desc')
            ->get();
        return response()->json($messages, 200);
    }

    public function getReceiverMessages(int $receiver_id)
    {
        $id = auth('sanctum')->user()->id;
        $messages = Message::where(function ($query) use ($id, $receiver_id) {
            $query->where('receiver_id', $id)
                ->where('sender_id', $receiver_id);
            })
            ->orderBy('time_sent', 'desc')
            ->get();
        return response()->json($messages, 200);
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

        $message = Message::create([
            'match_id' => $request->match_id,
            'sender_id' => $request->sender_id,
            'receiver_id' => $request->receiver_id,
            'message_content' => $request->message_content,
        ]);

        // Broadcast the message event
        // event(new MessageSent($request->user('sanctum'), $request->message_content));
        return response()->json($message, 201);
    }
}

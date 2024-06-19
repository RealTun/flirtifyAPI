<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\UserBlock;
use App\Models\UserPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Events\MessageSent;
use App\Models\User;

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

    public function getMessages(int $receiver_id)
    {
        $id = auth('sanctum')->user()->id;
        $messages = Message::where(function ($query) use ($id, $receiver_id) {
            $query->where('sender_id', $id)
                ->where('receiver_id', $receiver_id);
        })
            ->orWhere(function ($query) use ($id, $receiver_id) {
                $query->where('receiver_id', $id)
                    ->where('sender_id', $receiver_id);
            })
            ->orderBy('time_sent', 'asc')
            ->get();
        $photo = UserPhoto::where('user_account_id', $receiver_id)->first();

        $response = $messages->map(function ($message) use ($photo, $id) {
            return [
                'id' => $message->id,
                'match_id' => $message->match_id,
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->receiver_id,
                'message_content' => $message->message_content,
                'time_sent' => $message->time_sent,
                'imageReceiverUrl' => $photo->imageUrl(),
                'isSentByCurrentUser' => $id == $message->sender_id ? true : false
            ];
        });

        return response()->json($response, 200);
    }

    public function store(Request $request)
    {
        $rules = [
            'match_id' => 'required|exists:user_connection,id',
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

        $sender_id = $request->user('sanctum')->id;

        $message = Message::create([
            'match_id' => $request->match_id,
            'sender_id' => $sender_id,
            'receiver_id' => $request->receiver_id,
            'message_content' => $request->message_content,
        ]);

        $photo = UserPhoto::where('user_account_id', $request->receiver_id)->first();
        $data = Message::find($message->id);
        $response = [
            'id' => $message->id,
            'match_id' => $message->match_id,
            'sender_id' => $message->sender_id,
            'receiver_id' => $message->receiver_id,
            'message_content' => $message->message_content,
            'time_sent' => $data->time_sent,
            'imageReceiverUrl' => $photo->imageUrl(),
            'isSentByCurrentUser' => $sender_id == $message->sender_id ? true : false
        ];

        // Broadcast the message event
        event(new MessageSent($message->id, $message->match_id, $message->sender_id, $message->receiver_id, $message->message_content, $data->time_sent, $photo->imageUrl(), true));
        return response()->json($response, 201);
    }

    public function blockUser(Request $request)
    {
        $rules = [
            'user_account_id_blocked' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(["status" => "block error"], 400);
        }

        $data = [
            "user_account_id" => $request->user('sanctum')->id,
            "user_account_id_blocked" => $request->user_account_id_blocked,
        ];

        $isExisted = UserBlock::where($data)->exists();
        if($isExisted){
            return response()->json(["status" => "block error"], 400);
        }

        UserBlock::create($data);

        return response()->json(["status" => "block success"], 201);
    }

    public function unblockUser(int $user_id_blocked)
    {
        $id = auth('sanctum')->user()->id;
        $user = UserBlock::where("user_account_id_blocked", $user_id_blocked)
            ->where("user_account_id", $id)
            ->first();
        if ($user) {
            $user->delete();
            return response()->json(["status" => "unlock success"], 200);
        }
        return response()->json(["status" => "unlock error"], 400);
    }
}

<?php

namespace App\Events;

use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // public $message;
    // public $user;

    // public function __construct($user, $message)
    // {
    //     $this->message = $message;
    //     $this->user = $user;
    // }

    public $id;
    public $match_id; 
    public $sender_id;
    public $receiver_id;
    public $message_content;
    public $time_sent;
    public $imageReceiverUrl;
    public $isSentByCurrentUser;

    public function __construct($id, $match_id, $sender_id, $receiver_id, $message_content, $time_sent, $imageReceiverUrl, $isSentByCurrentUser)
    {
        $this->id = $id;
        $this->match_id = $match_id;
        $this->sender_id = $sender_id;
        $this->receiver_id = $receiver_id;
        $this->message_content = $message_content;
        $this->time_sent = $time_sent;
        $this->imageReceiverUrl = $imageReceiverUrl;
        $this->isSentByCurrentUser = $isSentByCurrentUser;
    }

    public function broadcastOn()
    {
        return ['chat'];
    }
  
    public function broadcastAs()
    {
        return 'chat-flirtify';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $table = 'message';
    public $timestamps = false;
    protected $fillable = [
        'match_id',
        'sender_id',
        'receiver_id',
        'message_content',
        'time_sent',
    ];

    public function match()
    {
        return $this->belongsTo(UserConnection::class, 'match_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}

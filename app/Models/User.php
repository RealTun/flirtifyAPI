<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'user_account';
    public $timestamps = false;
    protected $fillable = [
        'email',
        'phone',
        'pw',
        'fullname',
        'bio',
        'age',
        'gender',
        'looking_for',
        'location',
        'confirmation_code',
        'confirmation_time'
    ];

    protected $hidden = [
        'pw',
    ];
    
    public function photos()
    {
        return $this->hasMany(UserPhoto::class, 'user_account_id');
    }

    // Define the relationship with the UserConnection model as user1
    public function connectionsAsUser1()
    {
        return $this->hasMany(UserConnection::class, 'user1_id');
    }

    // Define the relationship with the UserConnection model as user2
    public function connectionsAsUser2()
    {
        return $this->hasMany(UserConnection::class, 'user2_id');
    }

    // Define the relationship with the Message model as the sender
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    // Define the relationship with the Message model as the receiver
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
}

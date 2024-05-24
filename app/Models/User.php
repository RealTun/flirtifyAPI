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
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBlock extends Model
{
    use HasFactory;
    protected $table = 'block_user';
    public $timestamps = false;

    protected $fillable = [
        'user_account_id',
        'user_account_id_blocked',
    ];
}

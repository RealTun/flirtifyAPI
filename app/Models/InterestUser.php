<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterestUser extends Model
{
    use HasFactory;
    protected $table = 'interest_user';
    public $timestamps = false;

    protected $fillable = [
        'interest_type_id',
        'user_account_id',
    ];

    public function userAccount()
    {
        return $this->belongsTo(User::class, 'user_account_id');
    }

    public function interestType()
    {
        return $this->belongsTo(InterestType::class, 'interest_type_id');
    }
}

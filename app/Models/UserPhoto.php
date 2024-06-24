<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPhoto extends Model
{
    use HasFactory;
    protected $table = 'user_photo';
    public $timestamps = false;

    protected $fillable = [
        'user_account_id',
        'link',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_account_id');
    }

    public function imageUrl(){
        // return env('CLOUDFLARE_R2_URL').'/'. $this->link;
        return $this->link;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preference extends Model
{
    use HasFactory;
    
    protected $table = 'preference';
    public $timestamps = false;

    protected $fillable = [
        'user_account_id',
        'min_age',
        'max_age',
        'max_distance'
    ];
}

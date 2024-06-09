<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterestType extends Model
{
    use HasFactory;
    protected $table = 'interest_type';
    public $timestamps = false;
    protected $fillable = [
        'name_interest_type',
    ];

    public function interestUsers()
    {
        return $this->hasMany(InterestUser::class, 'interest_type_id');
    }
}

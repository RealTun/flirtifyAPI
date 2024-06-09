<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterestedInRelation extends Model
{
    use HasFactory;
    protected $table = 'interested_in_relation';
    public $timestamps = false;

    protected $fillable = [
        'user_account_id',
        'relationship_type_id'
    ];

    public function userAccount()
    {
        return $this->belongsTo(User::class, 'user_account_id');
    }

    public function relationshipType()
    {
        return $this->belongsTo(RelationshipType::class, 'relationship_type_id');
    }
}

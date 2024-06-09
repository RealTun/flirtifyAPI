<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelationshipType extends Model
{
    use HasFactory;
    protected $table = 'relationship_type';
    
    public $timestamps = false;

    protected $fillable = [
        'name_relationship',
    ];

    public function interestedInRelations()
    {
        return $this->hasMany(InterestedInRelation::class, 'relationship_type_id');
    }
}

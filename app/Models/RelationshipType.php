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
}

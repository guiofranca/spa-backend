<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'owner_id',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_group');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function hasUser(User $user){
        return $this->users()->where('id', $user->id)->get()->isNotEmpty();
    }

    public function userIsOwner(User $user){
        return $this->owner_id == $user->getKey();
    }
}

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

    public function groupMembers(){
        return $this->hasMany(GroupMember::class);
    }

    public function hasMember(User $user){
        return $this->groupMembers()->where('user_id', $user->id)->get()->isNotEmpty();
    }

    public function userIsOwner(User $user){
        return $this->owner_id === $user->getKey();
    }
}

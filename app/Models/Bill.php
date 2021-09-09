<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'value',
        'user_id',
        'group_id',
        'category_id',
        'paid_at',
        'due_at',
    ];

    public function getValueAttribute()
    {
        return sprintf('%.2f', $this->attributes['value']/100);
    }

    public function setValueAttribute($value)
    {
        return $this->attributes['value'] = (int)($value*100);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function group(){
        return $this->belongsTo(Group::class);
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function isOwner(User $user){
        return $this->user_id == $user->getKey();
    }
}

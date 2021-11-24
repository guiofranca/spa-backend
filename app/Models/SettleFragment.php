<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettleFragment extends Model
{
    use HasFactory;

    protected $fillable = [
        'settle_id',
        'user_id',
        'paid',
        'contribute',
        'due',
        'to_receive',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPaidAttribute()
    {
        return sprintf('%.2f', $this->attributes['paid']/100);
    }

    public function setPaidAttribute($value)
    {
        return $this->attributes['paid'] = (int)($value*100);
    }

    public function getContributeAttribute()
    {
        return sprintf('%.2f', $this->attributes['contribute']/100);
    }

    public function setContributeAttribute($value)
    {
        return $this->attributes['contribute'] = (int)($value*100);
    }

    public function getDueAttribute()
    {
        return sprintf('%.2f', $this->attributes['due']/100);
    }

    public function setDueAttribute($value)
    {
        return $this->attributes['due'] = (int)($value*100);
    }

    public function getToReceiveAttribute()
    {
        return sprintf('%.2f', $this->attributes['to_receive']/100);
    }

    public function setToReceiveAttribute($value)
    {
        return $this->attributes['to_receive'] = (int)($value*100);
    }

}

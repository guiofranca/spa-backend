<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'group_id',
        'date',
        'settled',
    ];

    protected $casts = [
        'date' => 'date',
        'settled' => 'boolean',
    ];

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}

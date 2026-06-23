<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Recommendation extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'trip_id', 'rating', 'text'];


    protected $casts = [
        'rating' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function trip()
    {
        return $this->belongsTo(\App\Models\Trip::class);
    }
}

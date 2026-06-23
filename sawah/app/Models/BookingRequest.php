<?php

// app/Models/BookingRequest.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingRequest extends Model
{
    protected $fillable = [
        'booking_id','user_id','type','reason_code','note',
        'new_start_date','new_people_count','status','admin_id','admin_reason','resolved_at'
    ];

    protected $casts = [
        'new_start_date' => 'date',
        'resolved_at'    => 'datetime',
    ];
  public function trip(){ return $this->belongsTo(Trip::class); }

    public function booking(){ return $this->belongsTo(\App\Models\Booking::class); }
    public function user(){ return $this->belongsTo(\App\Models\User::class); }
    public function admin(){ return $this->belongsTo(\App\Models\User::class, 'admin_id'); }
}

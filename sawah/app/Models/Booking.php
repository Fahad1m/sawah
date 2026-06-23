<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
  protected $fillable = [
    'trip_id','user_id','customer_name','customer_email',
    'start_date','people_count','amount','status','payment_ref'
  ];

  protected $casts = ['start_date'=>'date','people_count'=>'integer','amount'=>'decimal:2'];

  public function trip(){ return $this->belongsTo(Trip::class); }
  public function user(){ return $this->belongsTo(User::class); }
  public function requests(){ return $this->hasMany(\App\Models\BookingRequest::class); }
public function getStartDateAttribute()
{
    return $this->attributes['start_date'] ?? $this->attributes['date'] ?? null;
}

}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripRequest extends Model {
    protected $fillable = [
        'user_name','user_email','destination','date','people','budget','status','details'
    ];
    protected $casts = ['date'=>'date','people'=>'integer','budget'=>'integer'];
}

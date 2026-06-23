<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;   
use Illuminate\Support\Str;

class Trip extends Model
{
    protected $fillable = ['destination','description','price','duration','rating','image','features'];
    protected $casts    = ['features' => 'array'];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if (!$this->image) return null;

        return Str::startsWith($this->image, ['http://','https://'])
            ? $this->image
            : asset('storage/'.$this->image);
    }
}

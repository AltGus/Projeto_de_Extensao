<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    protected $fillable = ['workshop_id', 'product', 'quantity'];

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }
}
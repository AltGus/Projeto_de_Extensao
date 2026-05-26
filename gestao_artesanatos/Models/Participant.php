<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $fillable = [
        'name',
        'age'
    ];

    public function workshops()
    {
        return $this->belongsToMany(Workshop::class);
    }
}
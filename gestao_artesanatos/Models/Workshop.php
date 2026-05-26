<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workshop extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    // Participantes da oficina (N:N)
    public function participants()
    {
        return $this->belongsToMany(Participant::class);
    }

    // Produções da oficina (1:N)
    public function productions()
    {
        return $this->hasMany(Production::class);
    }
}
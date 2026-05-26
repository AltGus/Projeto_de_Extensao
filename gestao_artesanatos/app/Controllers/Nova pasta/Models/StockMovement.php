<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'material_id',
        'quantity',
        'type'
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
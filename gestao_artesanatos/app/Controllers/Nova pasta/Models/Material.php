<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = [
        'name',
        'stock',
        'cost'
    ];

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
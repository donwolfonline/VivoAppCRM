<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstimateProduct extends Model
{
    protected $fillable = [
        'item',
        'estimate',
        'quantity',
        'tax',
        'discount',
        'total',
    ];

    public function items()
    {
        return $this->hasOne('App\Models\Item', 'id', 'item');
    }
}

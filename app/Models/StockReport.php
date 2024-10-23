<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockReport extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'type',
        'type_id',
        'description',
    ];


    public function item()
    {
        return $this->hasOne('App\Models\Item', 'id', 'product_id');
    }
}

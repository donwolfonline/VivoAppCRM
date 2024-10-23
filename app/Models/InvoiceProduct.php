<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceProduct extends Model
{
    protected $fillable = [
        'item',
        'invoice',
        'quantity',
        'tax',
        'discount',
        'total',
    ];

    public function items()
    {
        return $this->hasOne('App\Models\Item', 'id', 'item');
    }

    public function tax($taxes)
    {
        $taxArr = explode(',', $taxes);

        $taxes = [];
        foreach($taxArr as $tax)
        {
            $taxes[] = TaxRate::find($tax);
        }

        return $taxes;
    }
}

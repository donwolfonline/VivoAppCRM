<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'date',
        'amount',
        'client',
        'payment_method',
        'reference',
        'description',
        'created_by',
    ];

    public function clients()
    {
        return $this->hasOne('App\Models\User', 'id', 'client');
    }

    public function paymentMethods()
    {
        return $this->hasOne('App\Models\PaymentMethod', 'id', 'payment_method');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estimate extends Model
{
    protected $fillable = [
        'estimate',
        'client',
        'issue_date',
        'expiry_date',
        'send_date',
        'category',
        'status',
        'discount_apply',
        'created_by',
    ];

    public static $statues = [
        'Draft',
        'Open',
        'Sent',
        'Close',
    ];
    public static $statuesColor = [
        'Draft' => 'dark',
        'Open' => 'info',
        'Sent' => 'success',
        'Close' => 'danger',
    ];

    public function taxes1()
    {
        return $this->hasOne('App\Models\TaxRate', 'id', 'tax1');
    }

    public function taxes2()
    {
        return $this->hasOne('App\Models\TaxRate', 'id', 'tax2');
    }

    public function units()
    {
        return $this->hasOne('App\Models\Item', 'id', 'unit');
    }

    public function categories()
    {
        return $this->hasOne('App\Models\Item', 'id', 'category');
    }

    public function clients()
    {
        return $this->hasOne('App\Models\User', 'id', 'client');
    }

    public function clientDetail()
    {
        return $this->hasOne('App\Models\Client', 'user_id', 'client');
    }

    public function items()
    {
        $t = $this->hasMany('App\Models\EstimateProduct', 'estimate', 'id');
        return $t;
    }

    public function getSubTotal()
    {
        $subTotal = 0;
        foreach($this->items as $product)
        {
            $subTotal += ($product->price * $product->quantity);
        }

        return $subTotal;
    }

    public function getTotalTax()
    {
        $totalTax = 0;
        foreach($this->items as $product)
        {
            $taxes = Utility::totalTaxRate($product->tax);

            $totalTax += ($taxes / 100) * ($product->price * $product->quantity);
        }

        return $totalTax;
    }

    public function getTotalDiscount()
    {
        $totalDiscount = 0;
        foreach($this->items as $product)
        {
            $totalDiscount += $product->discount;
        }

        return $totalDiscount;
    }

    public function getTotal()
    {
        return ($this->getSubTotal() + $this->getTotalTax()) - $this->getTotalDiscount();
    }

    public function getDue()
    {
        $due = 0;
        foreach($this->payments as $payment)
        {
            $due += $payment->amount;
        }

        return ($this->getTotal() - $due) - $this->invoiceTotalCreditNote();
    }

}

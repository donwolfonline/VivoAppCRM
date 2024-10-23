<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class transactions extends Model
{
    use HasFactory;
    protected $fillable = [
        'referral_code',
        'used_referral_code',
        'company_name',
        'plane_name',
        'plan_price',
        'commission',
        'commission_amount',
        'uid',
    ];
    public function RefcompanyName()
    {
        return $this->hasOne('App\Models\User', 'referral_code', 'used_referral_code');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralProgram extends Model
{
    use HasFactory;
    protected $fillable = [
       'commission',
       'hold_amount',
       'guideline',
       'Reffral_enabled'

    ];
}


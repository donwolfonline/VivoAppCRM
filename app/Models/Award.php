<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
    protected $fillable = [
        'employee_id',
        'award_type',
        'date',
        'gift',
        'description',
        'created_by',
    ];

    public function awardType()
    {
        return $this->hasOne('App\Models\AwardType', 'id', 'award_type');
    }

    public function employee()
    {
        return $this->hasOne('App\Models\User', 'id', 'employee_id');
    }

    public static function employees($employees)
    {
        
        $categoryArr  = explode(',', $employees);
        $unitRate = 0;
        foreach($categoryArr as $employees)
        {
            $employees     = User::find($employees);
            $unitRate        = $employees->name;
        }

        return $unitRate;
    }

    public static function awardtypes($awardtype)
    {
        
        $categoryArr  = explode(',', $awardtype);
        $unitRate = 0;
        foreach($categoryArr as $awardtype)
        {
            $awardtype     = AwardType::find($awardtype);
            $unitRate        = $awardtype->name;
        }
        return $unitRate;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $fillable = [
        'department',
        'designation',
        'title',
        'date',
        'time',
        'notes',
        'created_by',
    ];



    public function departments()
    {
        return $this->hasOne('App\Models\Department', 'id', 'department');
    }



    public function designations()
    {
        return $this->hasOne('App\Models\Designation', 'id', 'designation');
    }



    public static function designation($desigantion)
    {
        $categoryArr  = explode(',', $desigantion);
        $unitRate = 0;
        foreach ($categoryArr as $desigantion) {
            $desigantion     = Designation::find($desigantion);
            $unitRate        = $desigantion->name;
        }

        return $unitRate;
    }



    public static function department($deapartment)
    {
        $categoryArr  = explode(',', $deapartment);
        $unitRate = 0;
        foreach ($categoryArr as $deapartment) {
            $deapartment     = Department::find($deapartment);
            $unitRate        = $deapartment->name;
        }

        return $unitRate;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appraisal extends Model
{
    protected $fillable = [
        'employee',
        'branch',
        'rating',
        'appraisal_date',
        'customer_experience',
        'marketing',
        'administration',
        'professionalism',
        'integrity',
        'attendance',
        'remark',
        'created_by',
    ];

    public static $technical = [
        'None',
        'Beginner',
        'Intermediate',
        'Advanced',
        'Expert / Leader',
    ];

    public static $organizational = [
        'None',
        'Beginner',
        'Intermediate',
        'Advanced',
    ];


    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'employee');
    }

    public function branches()
    {
        return $this->hasOne('App\Models\Branch', 'id', 'branch');
    }
    
    public function employees()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee');
    }
    
}

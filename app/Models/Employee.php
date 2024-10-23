<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'id',
        'user_id',
        'employee_id',
        'name',
        'branch_id',
        'department',
        'designation',
        'joining_date',
        'exit_date',
        'gender',
        'address',
        'mobile',
        'salary_type',
        'salary',
        'created_by',
        'branch_location',
        'bank_identifier_code',
        'bank_name',
        'account_number',
        'account_holder_name',
        'emergency_contact',
    ];

    public static $statues = [
        'Inactive',
        'Active',
    ];

    public function departments()
    {
        return $this->hasOne('App\Models\Department', 'id', 'department');
    }

    public function designations()
    {
        return $this->hasOne('App\Models\Designation', 'id', 'designation');
    }

    public function salaryType()
    {
        return $this->hasOne('App\Models\SalaryType', 'id', 'salary_type');
    }

    public function users()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function present_status($employee_id, $data)
    {
        return Attendance::where('employee_id', $employee_id)->where('date', $data)->first();
    }



}

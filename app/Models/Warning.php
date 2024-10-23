<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warning extends Model
{
    protected $fillable = [
        'warning_to',
        'warning_by',
        'subject',
        'warning_date',
        'description',
        'created_by',
    ];


    public function employee()
    {
        return $this->hasOne('App\Models\User', 'id', 'employee_id');
    }

    public function warningTo()
    {
        return $this->hasOne('App\Models\User', 'id', 'warning_to');

        // return User::where('id', $warningto)->first();
    }

    public function warningBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'warning_by');

        // return User::where('id', $warningby)->first();
    }
}

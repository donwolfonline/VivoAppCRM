<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'user_id',
        'client_id',
        'mobile',
        'company_name',
        'website',
        'tax_number',
        'notes',
        'address_1',
        'address_2',
        'city',
        'zip code',
        'state',
        'country',
        'created_by',
    ];

    public static $statues = [
        'Inactive',
        'Active',
    ];

    public function projects()
    { 
        return $this->hasOne('App\Models\Project', 'client', 'id');
    }
}

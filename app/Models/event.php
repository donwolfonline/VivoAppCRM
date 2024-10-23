<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class event extends Model
{
    protected $fillable = [
        'name',
        'color',
        'where',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'attendees',
        'created_by',
    ];
}

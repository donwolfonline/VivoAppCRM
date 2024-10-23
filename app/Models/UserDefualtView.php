<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDefualtView extends Model
{
    protected $fillable = [
        'module',
        'route',
        'view',
        'user_id',
    ];
}

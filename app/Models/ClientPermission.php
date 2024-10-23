<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPermission extends Model
{
    protected $fillable = [
        'client_id', 'project_id','permissions'
    ];
}

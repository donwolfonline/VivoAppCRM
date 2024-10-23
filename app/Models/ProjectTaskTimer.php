<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectTaskTimer extends Model
{
    protected $fillable = [
        'task_id',
        'start_time',
        'end_time',
    ];
}

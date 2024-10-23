<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectTaskComment extends Model
{
    protected $fillable = [
        'comment',
        'task_id',
        'created_by',
        'user_type',
    ];

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }
}

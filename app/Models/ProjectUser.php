<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectUser extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
    ];

    public function projectUsers()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'project_users', 'project_id', 'user_id')->orderBy('id', 'ASC');
    }
}

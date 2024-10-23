<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectClientFeedback extends Model
{
    protected $fillable = [
        'project_id',
        'file',
        'feedback',
        'feedback_by',
        'parent',
    ];

    public function feedbackUser()
    {
        return $this->hasOne('App\Models\User', 'id', 'feedback_by');
    }

    public function subFeedback()
    {
        return $this->hasMany('App\Models\ProjectClientFeedback', 'parent', 'id')->with('feedbackUser');
    }
}

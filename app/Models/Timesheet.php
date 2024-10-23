<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timesheet extends Model
{
    protected $fillable = [
        'project_id',
        'task_id',
        'employee',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'notes',
        'client_view',
        'created_by',
    ];

    public function users()
    {
        return $this->hasOne('App\Models\User', 'id', 'employee');
    }
    public function task()
    {
        return ProjectTask::where('id', '=', $this->task_id)->first();
    }

    public function tasks()
    {
        return $this->hasOne('App\Models\ProjectTask', 'id', 'task_id');
    }

    public function projects()
    {
        return $this->hasOne('App\Models\Project', 'id', 'project_id');
    }
}

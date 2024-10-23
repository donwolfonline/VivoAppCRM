<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeTracker extends Model
{
   

    protected $fillable = [
        'project_id',
        'task_id',
        'is_active',
        'name',
        'is_billable',
        'start_time',
        'end_time',
        'total_time',
        'created_by',

    ];

    protected $appends  = array(
        'project_name',
        'project_task',
        'total',
    );

    public function project()
    {
        return $this->hasOne('App\Models\Project', 'id', 'project_id');
    }

    public function task()
    {
        return $this->hasOne('App\Models\ProjectTask', 'id', 'task_id');
    }

    public function getProjectNameAttribute($value)
    {
        $project = Project::select('id', 'title')->where('id', $this->project_id)->first();

        return $project ? $project->title : '';
    }

    public function getProjectTaskAttribute($value)
    {
        $task = ProjectTask::select('id', 'title')->where('id', $this->task_id)->first();

        return $task ? $task->title : '';
    }

    public function getTotalAttribute($value)
    {
        $total = Utility::second_to_time($this->total_time);

        return $total ? $total : '00:00:00';
    }

}

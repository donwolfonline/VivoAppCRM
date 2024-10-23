<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectTask extends Model
{
    protected $fillable = [
        'title',
        'priority',
        'description',
        'due_date',
        'start_date',
        'assign_to',
        'hours',
        'project_id',
        'created_by',
        'milestone_id',
        'status',
        'order',
        'stage',
        'time_tracking',
    ];

    public function task_user()
    {
        return $this->hasOne('App\Models\User', 'id', 'assign_to');
    }

    public function taskUser()
    {
        return $this->hasOne('App\Models\User', 'id', 'assign_to');
    }
    public function project()
    {
        return $this->hasOne('App\Models\Project', 'id', 'project_id');
    }

    public function users()
    {
        return User::whereIn('id',explode(',',$this->assign_to))->get();
    }
    public function comments()
    {
        return $this->hasMany('App\Models\ProjectTaskComment', 'task_id', 'id')->orderBy('id', 'DESC');
    }

    public function taskFiles()
    {
        return $this->hasMany('App\Models\ProjectTaskFile', 'task_id', 'id')->orderBy('id', 'DESC');
    }

    public function taskCheckList()
    {
        return $this->hasMany('App\Models\ProjectTaskCheckList', 'task_id', 'id')->orderBy('id', 'DESC');
    }

    public function taskCompleteCheckListCount()
    {
        return $this->hasMany('App\Models\ProjectTaskCheckList', 'task_id', 'id')->where('status', '=', '1')->count();
    }

    public function taskTotalCheckListCount()
    {
        return $this->hasMany('App\Models\ProjectTaskCheckList', 'task_id', 'id')->count();
    }

    public function milestone()
    {
        return $this->hasOne('App\Models\ProjectMilestone', 'id', 'milestone_id');
    }
    public function milestones()
    {
      $data = $this->milestone_id ? ProjectMilestone::find($this->milestone_id) : null;
      return $data;
    }
    public function stages()
    {
        return $this->hasOne('App\Models\ProjectStage', 'id', 'stage');
    }


    public function taskTimer()
    {
        return $this->hasMany('App\Models\ProjectTaskTimer', 'task_id', 'id');
    }

    public function totalTime()
    {
        $hours = $minutes = 0;
        foreach($this->taskTimer as $timer)
        {
            $startTime = $timer->start_time;
            $endTime   = $timer->end_time;
            $totalTime = strtotime($endTime) - strtotime($startTime);

            $minut   = ($totalTime) / 60;
            $hours   += (int)$minut / 60;
            $minutes += $minut % 60;

        }
        $totalTaskhours = ($hours > 1) ? (int)$hours . " hrs" . ' ' . (int)$minutes . " min" : "$minutes Minutes";

        return $totalTaskhours;
    }

    public function taskTime($startTime, $endTime)
    {

        $totalTime = strtotime($endTime) - strtotime($startTime);

        $minut          = ($totalTime) / 60;
        $hours          = (int)$minut / 60;
        $minutes        = $minut % 60;
        $totalTaskhours = ($hours > 1) ? (int)$hours . " hrs" . ' ' . (int)$minutes . " min" : "$minutes Minutes";

        return $totalTaskhours;
    }
}

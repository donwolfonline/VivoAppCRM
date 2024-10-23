<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'title',
        'price',
        'client',
        'start_date',
        'due_date',
        'copylinksetting',
        'password',
        'hours',
        'description',
        'label',
        'lead',
        'status',
        'created_by',
    ];

    public static $status             = [
        'incomplete' => 'Incomplete',
        'complete' => 'Complete',
    ];
    public static $priority           = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
    ];
    public static $projectStatus      = [
        'not_started' => 'Not Started',
        'in_progress' => 'In Progress',
        'on_hold' => 'On Hold',
        'canceled' => 'Canceled',
        'finished' => 'Finished',
    ];
    public static $projectStatusColor = [
        'info',
        'secondary',
        'primary',
        'warning',
        'danger',
    ];

    // public static $permission     = [
    //     '',
    //     'show activity',
    //     'show milestone',
    //     'create milestone',
    //     'edit milestone',
    //     'delete milestone',
    //     'show task',
    //     'create task',
    //     'edit task',
    //     'delete task',
    //     'move task',
    //     'create checklist',
    //     'edit checklist',
    //     'delete checklist',
    //     'show checklist',
    //     'show uploading',
    //     'manage bug report',
    //     'create bug report',
    //     'edit bug report',
    //     'delete bug report',
    //     'move bug report',
    //     'manage timesheet',
    //     'create timesheet',
    //     'edit timesheet',
    //     'delete timesheet',
    // ];


    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'project_users', 'project_id', 'user_id')->orderBy('id', 'ASC');
    }
    public function clients()
    {
        return $this->hasOne('App\Models\User', 'id', 'client');
    }

    public function activities()
    {
        return $this->hasMany('App\Models\ProjectActivityLog', 'project_id', 'id')->orderBy('id', 'desc');
    }

    public function tasks()
    {
        if(\Auth::user()->type == 'employee')
        {
            return $this->hasMany('App\Models\ProjectTask', 'project_id', 'id')->where('assign_to', \Auth::user()->id)->with(['stages','taskUser']);
        }
        else
        {
            return $this->hasMany('App\Models\ProjectTask', 'project_id', 'id')->with(['stages','taskUser']);
        }
    }

    public function milestones()
    {
        return $this->hasMany('App\Models\ProjectMilestone', 'project_id', 'id');
    }

    public function user_project_total_task($project_id, $user_id)
    {
        return ProjectTask::where('project_id', '=', $project_id)->where('assign_to', '=', $user_id)->count();
    }
    public function countTask()
    {
        return ProjectTask::where('project_id', '=', $this->id)->count();
    }
    public function countTaskComments()
    {
        return ProjectTask::join('project_task_comments', 'project_task_comments.task_id', '=', 'project_tasks.id')->where('project_id', '=', $this->id)->count();
    }
    public function projectUser()
    {
        return ProjectUser::select('project_users.*', 'users.name', 'users.avatar', 'users.email', 'users.type')->join('users', 'users.id', '=', 'project_users.user_id')->where('project_id', '=', $this->id)->whereNotIn('user_id', [$this->created_by])->get();
    }
    public function label()
    {
        return $this->hasOne('App\Models\Label', 'id', 'label')->first();
    }
    public function client()
    {
        return $this->hasOne('App\Models\User', 'id', 'client')->first();
    }
    public function client_project_permission()
    {
        return ClientPermission::where('project_id', $this->id)->where('client_id', $this->client)->first();
    }
    public function project_complete_task($project_id, $last_stage_id)
    {
        return ProjectTask::where('project_id', '=', $project_id)->where('stage', '=', $last_stage_id)->count();
    }
    public function user_project_complete_task($project_id, $user_id, $last_stage_id)
    {
        return ProjectTask::where('project_id', '=', $project_id)->where('assign_to', '=', $user_id)->where('stage', '=', $last_stage_id)->count();
    }
    public function project_total_task($project_id)
    {
        return ProjectTask::where('project_id', '=', $project_id)->count();
    }
    public function project_last_stage()
    {
        return ProjectStage::where('created_by', '=', $this->created_by)->orderBy('order', 'desc')->first();
    }

    public function taskFilter($status, $priority, $dueDate)
    {
        $tasks                  = ProjectTask::where('project_id', $this->id);

        if(\Auth::user()->type == 'employee')
        {
            $tasks->where('assign_to', \Auth::user()->id);
        }
        if(!empty($status))
        {
            $tasks->where('stage', $status);
        }
        if(!empty($priority))
        {
            $tasks->where('priority', $priority);
        }
        if(!empty($dueDate))
        {
            $tasks->where('due_date', $dueDate);
        }
        $tasks                  = $tasks->get();

        return $tasks;
    }

    public function expenses()
    {
        return $this->hasMany('App\Models\Expense', 'project', 'id')->with('users');
    }

    public function tasksFilter()
    {
        $tasks                  = ProjectTask::where('project_id', $this->id);

        if((isset($_GET['start_date']) && !empty($_GET['start_date'])) && (isset($_GET['end_date']) && !empty($_GET['end_date'])))
        {
            $tasks->whereBetween(
                'start_date', [
                                $_GET['start_date'],
                                $_GET['end_date'],
                            ]
            );
        }
        else
        {
            $end_date           = date('Y-m-d');
            $start_date         = date('Y-m-d', strtotime('-30 days'));

            $tasks->whereBetween(
                'start_date', [
                                $start_date,
                                $end_date,
                            ]
            );
        }


        if(isset($_GET['employee']) && !empty($_GET['employee']))
        {
            $tasks->where('assign_to', $_GET['employee']);
        }

        $tasks                  = $tasks->get();

        return $tasks;
    }

    public function dueTask()
    {
        $tasks                  = ProjectTask::where('project_id', $this->id)->where('due_date', '<', date('Y-m-d'))->count();

        return $tasks;
    }

    public function userTasks()
    {
        $tasks                  = ProjectTask::where('project_id', $this->id)->where('assign_to', \Auth::user()->id)->get();

        return $tasks;
    }

    public function completedTask($stage_id = null)
    {
        if($stage_id == null)
        {
            $stage  = ProjectStage::where('created_by',\Auth::user()->creatorId())->orderBy('order', 'desc')->first();
            $stage  = $stage_id;
        }

        return ProjectTask::where('project_id', $this->id)->where('stage', (!empty($stage)) || ($stage_id != null) ? $stage_id : 0)->count();
    }

    public function totalExpense()
    {
        return Expense::where('project', $this->id)->sum('amount');
    }

     // For Delete project and it's based sub record
     public static function deleteProject($project_id)
     {
         $project               = Project::find($project_id);
         if($project)
         {
             $project->tasks()->delete();
         }
    }
    public static function getprojectname($projects)
    {
        $projectArr             = explode(',', $projects);
        $project                = '';
        foreach($projectArr as $projects)
        {
            $project_data       = Project::where('id',$projects)->first();
            $project           = $project_data->name.",";
        }

        return $project;
    }

    public function project_progress()
    {
            $total_task         = ProjectTask::where('project_id', '=', $this->id)->count();

            $completed_task     = ProjectTask::where('project_id', '=', $this->id)->where('stage', '=', 4)->count();
        // dd($completed_task);

            if($total_task > 0)
            {
                $percentage     = intval(($completed_task/$total_task) * 100);

                return [

                    'percentage' => $percentage . '%',
                           ];
                  }
                  else{
                     return [

                    'percentage' => 0,
                           ];

          }

    }
    public function project_milestone_progress()
    {
            $total_milestone    = ProjectMilestone::where('project_id', '=', $this->id)->count();
            $total_progress_sum = ProjectMilestone::where('project_id', '=', $this->id)->sum('progress');

            if($total_milestone > 0)
            {
                $percentage     = intval(($total_progress_sum /$total_milestone));


            return [

            'percentage' => $percentage . '%',
                   ];
          }
          else{
             return [

            'percentage' => 0,
                   ];

          }
    }

}

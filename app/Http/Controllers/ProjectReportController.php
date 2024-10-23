<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Utility;
use App\Models\ProjectTask;
use App\Models\ProjectStage;
use App\Models\ProjectMilestone;
use App\Models\Timesheet;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\UserDefualtView;
use App\Exports\task_reportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ProjectReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */   
     public function index()
    {
      
            $user = \Auth::user();
            if(\Auth::user()->type == 'super admin')
            {
                $users = User::where('created_by', '=', $user->creatorId())->where('type', '=', 'company')->get();
            }
            else
            {
                $users = User::where('created_by', '=', $user->creatorId())->where('type', '!=', 'client')->get();
            }
            
            if($user->type == 'client')
            {
                $projects = Project::where('client', '=', $user->id)->get();
     
            }
            elseif(\Auth::user()->type == 'employee')
            { 
                $projects = Project::select('projects.*')->leftjoin('project_users', 'project_users.project_id', 'projects.id')->where('project_users.user_id', '=', $user->id)->first();
            }
            else
            {
                $projects = $user->project;
               
            } 
            
            return view('project_report.index', compact('projects','users'));
    }
       
    
   
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
        $user = \Auth::user();

        if(\Auth::user()->type == 'super admin')
        {
            $users = User::where('created_by', '=', $user->creatorId())->where('type', '=', 'company')->get();
        }
        else
        {
            $users = User::where('created_by', '=', $user->creatorId())->where('type', '!=', 'client')->get();
          
        }
       
        if($user->type == 'client')
        {
            $project = Project::where('client', '=', $user->id)->where('id',$id)->first();
        }
        elseif(\Auth::user()->type == 'employee')
        {
            
            $project = Project::select('projects.*')->leftjoin('project_users', 'project_users.project_id', 'projects.id')->where('project_users.user_id', '=', $user->id)->first();

            // dd($project);
        }
        else
        {
            $project = Project::where('created_by', '=', $user->id)->where('id',$id)->first();
        } 
        
        if ($user) {
            $chartData = $this->getProjectChart(
                [    
                    'project_id' => $id,
                    'duration' => 'week',
                    ]
                );
                $daysleft = round((((strtotime($user->end_date) - strtotime(date('Y-m-d'))) / 24) / 60) / 60);
              
                // $project_status_task = Project::where('client', '=', $user->id)->where('id',$id)->get();


            

                $project_status_task = Projectstage::join("project_tasks", "project_tasks.stage", "=", "project_stages.id")->where('project_tasks.project_id', '=', $id)->groupBy('name')->selectRaw('count(project_tasks.stage) as count, name')->pluck('count', 'name');
            
                $totaltask = ProjectTask::where('project_id',$id)->count();
   
      

                $arrProcessPer_status_task = [];
                $arrProcess_Label_status_tasks = [];
                foreach ($project_status_task as $lables => $percentage_stage) {
                     $arrProcess_Label_status_tasks[] = $lables;
                    if ($totaltask == 0) {
                        $arrProcessPer_status_task[] = 0.00;
                    } else {
                        $arrProcessPer_status_task[] = round(($percentage_stage * 100) / $totaltask, 2);
                    }
                }

                
                $project_priority_task = ProjectTask::where('project_id',$id)->groupBy('priority')->selectRaw('count(id) as count, priority')->pluck('count', 'priority');

                $arrProcessPer_priority = [];
                $arrProcess_Label_priority = [];
                foreach ($project_priority_task as $lable => $process) {
                     $arrProcess_Label_priority[] = $lable;
                    if ($totaltask == 0) {
                        $arrProcessPer_priority[] = 0.00;
                    } else {
                        $arrProcessPer_priority[] = round(($process * 100) / $totaltask, 2);
                    }
                }
                $arrProcessClass = [
                    'text-success',
                    'text-primary',
                    'text-danger',
                ];
                
                  $chartData = app('App\Http\Controllers\ProjectController')->getProjectChart([
                    'created_by' =>$id,
                    'duration' => 'week',
                ]);
          
                // $stages = ProjectStage::all();
                $stages = ProjectStage::where('created_by', '=', $user->id)->get();
                // dd($stages);
                $milestones = ProjectMilestone::where('project_id' ,$id)->get();
                
   
   
                $logged_hour_chart = 0;
                $total_hour = 0;
                $logged_hour = 0;
       
               $tasks = ProjectTask::where('project_id',$id)->get();
       
               $data = [];
               foreach ($tasks as $task) {

        
               $timesheets_task = Timesheet::where('task_id',$task->id)->where('project_id',$id)->get(); 
               foreach($timesheets_task as $timesheet){
                
                               
                 $start_time = $timesheet->start_time;
                 $end_time = $timesheet->end_time;
                 $secs = strtotime($start_time);
                $hourdiff = date("H:i",strtotime($end_time)-$secs);
                $hours =  date('H', strtotime($hourdiff));
                $minutes =  date('i', strtotime($hourdiff));
                $total_hour = $hours + ($minutes/60) ;
                $logged_hour += $total_hour ;
                $logged_hour_chart = number_format($logged_hour, 2, '.', '');
               }   
           }


            //Estimated Hours
            $esti_logged_hour_chart = ProjectTask::where('project_id',$id)->sum('hours');
        return view('project_report.show', compact('user','users', 'arrProcessPer_status_task','arrProcess_Label_priority','esti_logged_hour_chart','logged_hour_chart','arrProcessPer_priority','arrProcess_Label_status_tasks','project','milestones', 'daysleft','chartData','arrProcessClass','stages'));
    
     }
    }
    /**
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function ajax_data(Request $request)
    {  
     
        $user = \Auth::user();

        if($user->type == 'client')
        {
            $projects = Project::where('client', '=', $user->id);
            
        }
        elseif(\Auth::user()->type == 'employee')
        {
            $projects = Project::select('projects.*')->leftjoin('project_users', 'project_users.project_id', 'projects.id')->where('project_users.user_id', '=', $user->id);
  
        }
        else
        {
            $projects =  Project::where('created_by', $user->id);
        }           

        if ($request->all_users) {
    
            unset($projects);
            $UserEmailTemp = ProjectUser::where('user_id',$request->all_users)->pluck('project_id');
            $projects = Project::whereIn('id',$UserEmailTemp);
        }
        if ($request->status) {
            $projects->where('status', '=', $request->status);
        }
        if ($request->start_date) {
            $projects->where('start_date', '=', $request->start_date);
        }

        if ($request->due_date) {
            $projects->where('due_date', '=', $request->due_date);
        }
        
        $projects = $projects->get();

        $data = [];
        foreach($projects as $project) {
       
            $tmp = [];
            // $tmp['id'] = $project->id;
            $tmp['title'] = $project->title;
            $tmp['start_date'] = $project->start_date;
            $tmp['due_date'] = $project->due_date;
            $tmp['members'] = '<div class="user-group mx-2">';

            foreach($project->users as $projectUser){
                $avatar = $projectUser->avatar ? 'src="'.\App\Models\Utility::get_file('uploads/avatar/'). $projectUser->avatar.'"':'src="'.asset(\Storage::url('avatar/avatar.png')).'"';
             if($user->is_active){
                                $tmp['members'] .= 
                               '
                              
                                      <a href="#" class="img_group" data-toggle="tooltip" data-placement="top" title=" '.$projectUser->name.'">
                                         <img alt="'.$projectUser->name.'" '.$avatar.'/>  
                                     </a> ';
                                   
                                 }
                             }
            $tmp['members'] .=   '</div>';
            $percentage = $project->project_progress();           
            // dd($percentage);
            $tmp['Progress'] = 
                '<div class="progress_wrapper">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" 
                        style="width:'.$percentage["percentage"].'"
                            aria-valuenow="55" aria-valuemin="0" aria-valuemax="100">
                            </div>
                    </div>
                    <div class="progress_labels">
                        <div class="total_progress">
                        
                            <strong>'.$percentage["percentage"].'</strong>
                        </div>
                    
                    </div>
                </div>';
            if ($project->status == 'not_started') {
                $tmp['status'] = '<span class="badge rounded-pill p-2 px-3  bg-success">' .'Not Started' . '</span>';
            } 
            elseif($project->status == 'in_progress') {
                $tmp['status'] = '<span class="badge rounded-pill p-2 px-3  bg-secondary">' . 'In Progress' . '</span>';
            }
            elseif($project->status == 'on_hold') {
                $tmp['status'] = '<span class="badge rounded-pill p-2 px-3  bg-secondary">' . 'On Hold' . '</span>';
            }
            elseif($project->status == 'canceled') {
                $tmp['status'] = '<span class="badge rounded-pill p-2 px-3  bg-secondary">' . 'Canceled' . '</span>';
            }
            else {
                $tmp['status'] = '<span class="badge rounded-pill p-2 px-3  bg-warning">' . 'Finished'. '</span>';
            }
            if (\Auth::user()->type != 'client') {
                $tmp['action'] = '
                <a  class="action-btn btn-warning  btn btn-sm d-inline-flex align-items-center" data-toggle="popover"  title="' . __('view Project Report') . '" data-size="lg" data-title="' . __('show') . '" href="' . route('project_report.show',[
                        $project->id,
                    ]
                ) . '"><i class="ti ti-eye"></i></a>


                <a href="' . route('project.edit',\Crypt::encrypt($project->id)) . '" class="action-btn btn-info  btn btn-sm d-inline-flex align-items-center" data-toggle="popover"  title="' . __('Edit Project') . '" data-ajax-popup="true" data-size="lg" data-title="' . __('Edit') . '" data-url=""><i class="ti ti-pencil"></i></a>';
            }else
            {
                $tmp['action'] = '
                <a  class="action-btn btn-warning  btn btn-sm d-inline-flex align-items-center" data-toggle="popover"  title="' . __('view Project') . '" data-size="lg" data-title="' . __('show') . '" href="' . route('project_report.show', [
                        $project->id,
                    ]
                ) . '"><i class="ti ti-eye"></i></a>';

            }
            
            $data[] = array_values($tmp);
        }

        return response()->json(['data' => $data], 200);
    }
public function getProjectChart($arrParam)
    {
        $arrDuration = [];
        if ($arrParam['duration'] && $arrParam['duration'] == 'week') {
            $previous_week = Utility::getFirstSeventhWeekDay(-1);
            foreach ($previous_week['datePeriod'] as $dateObject) {
                $arrDuration[$dateObject->format('Y-m-d')] = $dateObject->format('D');
            }
        }

    $arrTask = [
        'label' => [],
        'color' => [],
    ];
    

    foreach ($arrDuration as $date => $label) {
        $objProject = ProjectTask::select('status', \DB::raw('count(*) as total'))->whereDate('updated_at', '=', $date)->groupBy('status');

        if (isset($arrParam['project_id'])) {
            $objProject->where('project_id', '=', $arrParam['project_id']);
        }
        if (isset($arrParam['created_by'])) {
            $objProject->whereIn(
                'project_id', function ($query) use ($arrParam) {
                    $query->select('id')->from('projects')->where('created_by', '=', $arrParam['created_by']);
                }
            );
        }
        $data = $objProject->pluck('total', 'status')->all();

  
        $arrTask['label'][] = __($label);
   
    return $arrTask;
    }
}
public function ajax_tasks_report(Request $request,$id)
{
    // dd('d');
    $userObj = \Auth::user();
    $tasks = ProjectTask::where('project_id','=',$id);
   
    if ($request->assign_to) {
        $tasks->whereRaw("find_in_set('" . $request->assign_to . "',assign_to)");
    }

    if ($request->priority) {
        $tasks->where('priority', '=', $request->priority);
    }

     if ($request->milestone_id) {
        $tasks->where('milestone_id', '=', $request->milestone_id);
    }
    if ($request->stage) {
        $tasks->where('stage', '=', $request->stage);
    }

     if ($request->start_date) {
        $tasks->where('start_date', '=', $request->status);
    }

    if ($request->due_date) {
        $tasks->where('due_date', '=', $request->due_date);
    }

    $tasks = $tasks->get();


      

        $data = [];
        foreach ($tasks as $task) {
            $timesheets_task = Timesheet::where('project_id',$id)->where('task_id' ,$task->id)->get(); 
             $total_hour = 0;
             $logged_hour = 0;
            $hour_format_number = 0;

            foreach($timesheets_task as $timesheet){
                $start_time = $timesheet->start_time;
                 $end_time = $timesheet->end_time;
                 $secs = strtotime($start_time);
                $hourdiff = date("H:i",strtotime($end_time)-$secs);
                $hours =  date('H', strtotime($hourdiff));
                $minutes =  date('i', strtotime($hourdiff));
                $total_hour = $hours + ($minutes/60) ;
                $logged_hour += $total_hour ;
              
                $hour_format_number = number_format($logged_hour, 2, '.', '');
                
            }              

            $tmp = [];
            $tmp = [];
            // $tmp['title'] =  $task->title;
            $tmp['title'] = '<a href="#" data-bs-whatever="View Task" data-size="lg" data-url="'.route("project.task.show",$task->id).'"  data-bs-toggle="modal" . data-bs-target="#exampleModal"> ' . $task->title .'</a>';

           
            $tmp['milestone'] = ($milestone = $task->milestones()) ? $milestone->title : '';
        

            $start_date = '<span class="text-body">' . date('Y-m-d', strtotime($task->start_date)) . '</span> ';

            $due_date = '<span class="text-' . ($task->due_date < date('Y-m-d') ? 'danger' : 'success') . '">' . date('Y-m-d', strtotime($task->due_date)) . '</span> ';
            $tmp['start_date'] = $start_date;
            $tmp['due_date'] = $due_date;

            
                $tmp['user_name'] = "";
                foreach ($task->users() as $user) {
                    if (isset($user) && $user) {
                        $tmp['user_name'] .= '<span class="badge bg-secondary p-2 px-3 rounded">' . $user->name . '</span> ';
                    }
                }
           
            $tmp['logged_hours'] = $hour_format_number;

           
            if ($task->priority == "high") {
                $tmp['priority'] = '<span class="priority_badge badge bg-danger p-2 px-3 rounded" style="width: 77px;">' . __('High') . '</span>';
            } elseif ($task->priority == "medium") {
                $tmp['priority'] = '<span class="priority_badge badge bg-info p-2 px-3 rounded" style="width: 77px;">' . __('Medium') . '</span>';
            } else {
                $tmp['priority'] = '<span class="priority_badge badge bg-success p-2 px-3 rounded" style="width: 77px;">' . __('Low') . '</span>';
            }
            
            if ($task->complete == 1) {
            $tmp['stage'] = '<span class="status_badge badge bg-success p-2 px-3 rounded" style="width: 87px;">' . __($task->stages->name) . '</span>';
            } else {
                $tmp['stage'] = '<span class="status_badge badge bg-primary p-2 px-3 rounded" style="width: 87px;">' . __($task->stages->name) . '</span>';
            } 
            $data[] = array_values($tmp);
            unset($hour_format_number);
        }
       
        return response()->json(['data' => $data], 200);
        }
        public function export($id)
            {

                $name = 'task_report_' . date('Y-m-d i:h:s');
                $data = Excel::download(new task_reportExport($id), $name . '.xlsx');

                return $data;
            }

}

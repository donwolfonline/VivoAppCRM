<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CheckList;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Label;
use App\Models\TimeTracker;
use App\Models\Lead;
use App\Models\Project;
use App\Models\ProjectActivityLog;
use App\Models\ProjectClientFeedback;
use App\Models\ProjectComment;
use App\Models\ProjectFile;
use App\Models\ProjectMilestone;
use App\Models\ProjectNote;
use App\Models\ProjectStage;
use App\Models\ProjectTask;
use App\Models\ProjectTaskCheckList;
use App\Models\ProjectTaskComment;
use App\Models\ProjectTaskFile;
use App\Models\ProjectTaskTimer;
use App\Models\ProjectUser;
use App\Models\Timesheet;
use App\Models\User;
use App\Models\UserDefualtView;
use App\Models\Utility;
use App\Models\Employee;
use App\Models\ClientPermission;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\GoogleCalendar\Event as GoogleEvent;


class ProjectController extends Controller
{
    public function index()
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client' || \Auth::user()->type == 'employee') {
            $user = \Auth::user();
            if (\Auth::user()->type == 'client') {
                $projects = Project::where('client', '=', $user->id)->with('tasks')->get();
            } elseif (\Auth::user()->type == 'employee') {
                $projects = Project::select('projects.*')->leftjoin('project_users', 'project_users.project_id', 'projects.id')->where('project_users.user_id', '=', $user->id)->with('tasks')->get();
            } else {
                $projects = Project::where('created_by', '=', $user->creatorId())->with('tasks')->get();
            }


            $projectStatus = [
                'not_started' => __('Not Started'),
                'in_progress' => __('In Progress'),
                'on_hold' => __('On Hold'),
                'canceled' => __('Canceled'),
                'finished' => __('Finished'),
            ];

            $defualtView         = new UserDefualtView();
            $defualtView->route  = \Request::route()->getName();
            $defualtView->module = 'project';
            $defualtView->view   = 'list';
            User::userDefualtView($defualtView);

            $stage  = ProjectStage::where('created_by',\Auth::user()->creatorId())->orderBy('order', 'desc')->first();
            $stage_id = $stage->id;  

            return view('project.index', compact('projects', 'projectStatus','stage_id'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function copylink_setting_create($projectID)
    {
        $objUser = Auth::user();
        $project = Project::select('projects.*')->join('project_users', 'projects.id', '=', 'project_users.project_id')->where('project_users.user_id', '=', $objUser->id)->where('projects.id', '=', $projectID)->first();
        $result = json_decode($project->copylinksetting);
        return view('project.copylink_setting', compact('project', 'projectID', 'result'));
    }

    public function copylinksetting(Request $request, $id)
    {
        $objUser = Auth::user();
        // $id = Crypt::decryptString($id);
        $data = [];
        $data['basic_details']  = isset($request->basic_details) ? 'on' : 'off';
        $data['task']  = isset($request->task) ? 'on' : 'off';
        $data['ganttTasks']  = isset($request->ganttTasks) ? 'on' : 'off';
        $data['milestone']  = isset($request->milestone) ? 'on' : 'off';
        $data['note']   =  isset($request->note) ? 'on' : 'off';
        $data['files']  =  isset($request->files) ? 'on' : 'off';
        $data['comments']  = isset($request->comments) ? 'on' : 'off';
        $data['progress']  = isset($request->progress) ? 'on' : 'off';
        $data['feedbacks']  = isset($request->feedbacks) ? 'on' : 'off';
        $data['invoice']  = isset($request->invoice) ? 'on' : 'off';
        $data['timesheet']  = isset($request->timesheet) ? 'on' : 'off';
        $data['payment']  = isset($request->payment) ? 'on' : 'off';
        $data['expenses']  = isset($request->expenses) ? 'on' : 'off';
        $data['tracker_details']  = isset($request->tracker_details) ? 'on' : 'off';
        $data['password_protected']  = isset($request->password_protected) ? 'on' : 'off';
        $project = Project::select('projects.*')
            ->join('project_users', 'projects.id', '=', 'project_users.project_id')
            ->where('project_users.user_id', '=', $objUser->id)
            ->where('projects.id', '=', $id)->first();

        if (isset($request->password_protected) && $request->password_protected == 'on') {
            $project->password = base64_encode($request->password);
        } else {
            $project->password = null;
        }

        $project->copylinksetting = (count($data) > 0) ? json_encode($data) : null;
        $project->save();

        return redirect()->back()->with('success', __('Copy Link Setting Save Successfully!'));
    }

    public function projectPassCheck(Request $request, $id)
    {
        $id = \Illuminate\Support\Facades\Crypt::decrypt($id);

        $project = Project::find($id);

        if (($request->password == base64_decode($project->password))) {
            $ps_status = base64_encode('true');
            return redirect()->route('project.link', [$id, $ps_status]);
        } else {
            $ps_status = base64_encode('false');
            return redirect()->route('project.link', [$id, $ps_status]);
        }
    }

    public function projectlink(Request $request, $project_id, $ps_status = null,)
    {
        $projectID = \Illuminate\Support\Facades\Crypt::decrypt($project_id);

        $project = Project::find($projectID);

        if (Auth::user() != null) {
            $objUser         = Auth::user();
        } else {
            $objUser         = User::where('id', $project->created_by)->first();
        }
        // $projectID=\Illuminate\Support\Facades\Crypt::decrypt($projectID);
        $data = [];

        $data['basic_details']  = isset($request->basic_details) ? 'on' : 'off';
        $data['task']  = isset($request->task) ? 'on' : 'off';
        $data['payment']  = isset($request->payment) ? 'on' : 'off';
        $data['ganttTasks']  = isset($request->ganttTasks) ? 'on' : 'off';
        $data['files']  = isset($request->files) ? 'on' : 'off';
        $data['comments']  = isset($request->comments) ? 'on' : 'off';
        $data['milestone']  = isset($request->milestone) ? 'on' : 'off';
        $data['feedbacks']  = isset($request->feedbacks) ? 'on' : 'off';
        $data['expenses']  = isset($request->expenses) ? 'on' : 'off';
        $data['attachment']  = isset($request->attachment) ? 'on' : 'off';
        $data['invoice']  = isset($request->invoice) ? 'on' : 'off';
        $data['note']  = isset($request->note) ? 'on' : 'off';
        $data['tracker_details']  = isset($request->tracker_details) ? 'on' : 'off';
        $data['timesheet']  = isset($request->timesheet) ? 'on' : 'off';
        $data['password_protected']  = isset($request->password_protected) ? 'on' : 'off';
        // dd($project->created_by);

        $projects = Project::select('projects.*')->join('project_users', 'projects.id', '=', 'project_users.project_id')->where('project_users.user_id', '=', $objUser->id)->where('projects.id', '=', $projectID)->first();
        if ($projects) {
            //Basic Details
            $project_status = Project::$projectStatus;

            $notes      = ProjectNote::where('project_id', $projectID)->get();

            //chartdata
            $chartData = $this->getProjectChart(
                [
                    'created_by' => $project->created_by,
                    'project_id' => $projectID,
                    'duration' => 'week',
                ]
            );

            if (date('Y-m-d') == $project->due_date || date('Y-m-d') >= $project->due_date) {
                $daysleft = 0;
            } else {
                $daysleft = round((((strtotime($project->due_date) - strtotime(date('Y-m-d'))) / 24) / 60) / 60);
            }

            $timesheets = Timesheet::where('project_id', $projectID)->where('created_by', '=', $objUser->id)->get();
            $notes      = ProjectNote::where('project_id', $projectID)->get();
            $invoices     = Invoice::where('project', $projectID)->where('type', 'Project')->get();
            $feedbacks  = ProjectClientFeedback::where('project_id', $projectID)->where('parent', 0)->get();
            $totalExpense = Expense::where('project', $projectID)->sum('amount');
            $stages      = Projectstage::where('created_by', '=', $objUser->id)->orderBy('order', 'ASC')->get();
            $files      = ProjectFile::where('project_id', $projectID)->get();
            $comments   = ProjectComment::where('project_id', $projectID)->where('parent', 0)->get();


            if (!empty($project) && $project->created_by == $objUser->id) {
                if ($objUser->type != 'company') {
                    $arrProjectUsers = $project->projectUser()->pluck('user_id')->toArray();
                    array_push($arrProjectUsers, $project->client);

                    if (!in_array($objUser->id, $arrProjectUsers)) {
                        return redirect()->back()->with('error', __('Permission denied.'));
                    }
                }
            }

            //chartdata
            $duration = 'week';

            $ganttTasks = $this->getProjectChart(
                [
                    'project_id' => $projectID,
                    'duration' => 'week',
                ]
            );

            if (date('Y-m-d') == $project->end_date || date('Y-m-d') >= $project->end_date) {
                $daysleft = 0;
            } else {
                $daysleft = round((((strtotime($project->end_date) - strtotime(date('Y-m-d'))) / 24) / 60) / 60);
            }


            if (!empty($project) && $project->created_by == $objUser->id) {

                if ($objUser->type != 'company') {
                    $arrProjectUsers = $project->projectUser()->pluck('user_id')->toArray();
                    array_push($arrProjectUsers, $project->client);
                    if (!in_array($objUser->id, $arrProjectUsers)) {
                        return redirect()->back()->with('error', __('Permission denied.'));
                    }
                }
            }

            if (\Session::get('copy_pass_true' . $projectID) == $project->password . '-' . $projectID) {
                return view('project.copylink', compact('data', 'daysleft', 'project_status', 'project', 'projects', 'objUser', 'stages', 'timesheets', 'notes', 'invoices', 'feedbacks', 'totalExpense', 'files', 'comments', 'ganttTasks', 'duration'));
            } else {
                if (!isset(json_decode($project->copylinksetting)->password_protected) || json_decode($project->copylinksetting)->password_protected != 'on') {
                    return view('project.copylink', compact('projectID', 'daysleft', 'project_status', 'project', 'projects', 'objUser', 'stages', 'timesheets', 'notes', 'invoices', 'feedbacks', 'totalExpense', 'files', 'comments', 'ganttTasks', 'duration'));
                } elseif (isset(json_decode($project->copylinksetting)->password_protected) && json_decode($project->copylinksetting)->password_protected == 'on' && $request->password == base64_decode($project->password)) {
                    \Session::put('copy_pass_true' . $projectID, $project->password . '-' . $projectID);

                    return view('project.copylink', compact('data', 'daysleft', 'project_status', 'project', 'projects', 'objUser', 'stages', 'timesheets', 'notes', 'invoices', 'feedbacks', 'totalExpense', 'files', 'comments', 'ganttTasks', 'duration'));
                } else {
                    return view('project.copylink_password', compact('projectID', 'daysleft', 'project_status', 'project', 'projects', 'objUser', 'stages', 'timesheets', 'notes', 'invoices', 'feedbacks', 'totalExpense', 'files', 'comments', 'ganttTasks', 'duration'));
                }
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function clientPermission($project_id, $client_id)
    {
        $client   = User::find($client_id);
        $project  = Project::find($project_id);
        $selected = $client->clientPermission($project->id);
        if ($selected) {
            $selected = explode(',', $selected->permissions);
        } else {
            $selected = [];
        }
        $permissions = Project::$permission;

        return view('clients.create', compact('permissions', 'project_id', 'client_id', 'selected'));
    }
    public function storeClientPermission(request $request, $project_id, $client_id)
    {
        $this->validate(
            $request,
            [
                'permissions' => 'required',
            ]
        );

        $project = Project::find($project_id);
        if ($project->created_by == \Auth::user()->creatorId()) {
            $client      = User::find($client_id);
            $permissions = $client->clientPermission($project->id);
            if ($permissions) {
                $permissions->permissions = implode(',', $request->permissions);
                $permissions->save();
            } else {
                ClientPermission::create(
                    [
                        'client_id' => $client->id,
                        'project_id' => $project->id,
                        'permissions' => implode(',', $request->permissions),
                    ]
                );
            }

            return redirect()->back()->with('success', __('Permissions successfully updated.'))->with('status', 'clients');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'))->with('status', 'clients');
        }
    }

    public function changeLangcopylink($lang)
    {
        \Cookie::queue('LANGUAGE', $lang, 120);

        return redirect()->back()->with('success', __('Language Change Successfully!'));
    }

    public function create()
    {
        $employees = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'employee')->get()->pluck('name', 'id');
        $clients   = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'client')->get()->pluck('name', 'id');
        $clients->prepend('Select Client', '');
        $labels = Label::where('created_by', '=', \Auth::user()->creatorId())->get();
        $labels->prepend('Select Lead', '');
        $leads = Lead::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $leads->prepend('Select Lead', 0);
        $categories = Category::where('created_by', '=', \Auth::user()->creatorId())->where('type', 2)->get()->pluck('name', 'id');
        $categories->prepend('Select Category', '');

        $projectStatus = [
            'not_started' => __('Not Started'),
            'in_progress' => __('In Progress'),
            'on_hold' => __('On Hold'),
            'canceled' => __('Canceled'),
            'finished' => __('Finished'),
        ];


        return view('project.create', compact('clients', 'labels', 'employees', 'leads', 'categories', 'projectStatus'));
    }


    public function store(Request $request)
    {
        // dd($request);
        if (\Auth::user()->type == 'company') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'price' => 'required',
                    'start_date' => 'required',
                    'client' => 'required',
                    'category' => 'required',
                    'due_date' => 'required',
                    'employee' => 'required',
                    'status' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('project.index')->with('error', $messages->first());
            }

            $projectStages = ProjectStage::where('created_by', \Auth::user()->creatorId())->first();

            if (empty($projectStages)) {
                return redirect()->route('project.index')->with('error', 'Please add constant project stage.');
            }
            $project              = new Project();
            $project->title       = $request->title;
            $project->category    = $request->category;
            $project->price       = $request->price;
            $project->start_date  = $request->start_date;
            $project->due_date    = $request->due_date;
            $project->lead        = $request->lead;
            $project->client      = $request->client;
            $project->status      = $request->status;
            $project->description = $request->description;
            $project->created_by  = \Auth::user()->creatorId();
            $project->copylinksetting    = '{"basic_details":"on","task":"on","ganttTasks":"off","milestone":"on","note":"off","files":"off","comments":"off","progress":"off","feedbacks":"off","invoice":"on","timesheet":"off","payment":"off","expenses":"off","tracker_details":"off","password_protected":"off"}';
            $project->save();
            $projectUser             = new ProjectUser();
            $projectUser->user_id    = \Auth::user()->creatorId();
            $projectUser->project_id = $project->id;
            $projectUser->save();

            foreach ($request->employee as $key => $user) {
                $projectUser             = new ProjectUser();
                $projectUser->user_id    = $user;
                $projectUser->project_id = $project->id;
                $projectUser->save();
            }

            $client     = User::find($request->client);
            $user       = \Auth::user();
            $lead       = Lead::find($request->lead);
            $projectArr = [
                'project_title' => $project->title,
                'project_category' => !empty(Category::find($project->category)) ? Category::find($project->category)->name : '',
                'project_price' => $user->priceFormat($project->price),
                'project_client' =>  !empty(($client->name)) ? ($client->name) : '',

                'project_start_date' => $user->dateFormat($project->start_date),
                'project_due_date' => $user->dateFormat($project->due_date),
                'project_lead' => !empty($lead) ? $lead->subject : '-',
            ];

            // Send Email
            if (empty($client)) {
                $resp = '';
            } else {
                $resp = Utility::sendEmailTemplate('new_project', [$client->id => $client->email], $projectArr);
            }


            foreach ($request->employee as $key => $emp) {
                $employee         = User::find($emp);
                $projectAssignArr = [
                    'project_title' => $project->title,
                    'project_category' => !empty(Category::find($project->category)) ? Category::find($project->category)->name : '',
                    'project_price' => $user->priceFormat($project->price),
                    'project_client' => !empty(($client->name)) ? ($client->name) : '',
                    'project_assign_user' => $employee->name,
                    'project_start_date' => $user->dateFormat($project->start_date),
                    'project_due_date' => $user->dateFormat($project->due_date),
                    'project_lead' => !empty($lead) ? $lead->subject : '-',
                ];

                if (!empty($employee)) {
                    $resp = Utility::sendEmailTemplate('project_assigned', [$employee->id => $employee->email], $projectAssignArr);
                } else {
                    $resp = '';
                }
            }
            $settings  = Utility::settings();

            if (isset($settings['project_create_notification']) && $settings['project_create_notification'] == 1) {
                $uArr = [
                    'user_name' => \Auth::user()->name,
                    'project_name' => $request->title
                ];
                //  dd($uArr);
                Utility::send_slack_msg('new_project', $uArr);
            }
            if (isset($settings['telegram_project_create_notification']) && $settings['telegram_project_create_notification'] == 1) {
                $uArr = [
                    'user_name' => \Auth::user()->name,
                    'project_name' => $request->title
                ];
                //  dd($uArr);
                Utility::send_telegram_msg('new_project', $uArr);
            }

            $employee = Employee::where('user_id', $request->employee)->first();
            if (isset($settings['twilio_project_create_notification']) && $settings['twilio_project_create_notification'] == 1) {
                $uArr = [
                    'user_name' => \Auth::user()->name,
                    'project_name' => $request->title
                ];
                //  dd($uArr);
                Utility::send_twilio_msg('new_project', $uArr);
            }

            return redirect()->route('project.index')->with('success', __('Project successfully created.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show($ids, $duration = 'Week')
    {
        try {
            $id      = \Crypt::decrypt($ids);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Project Not Found.'));
        }

        // $id             = \Crypt::decrypt($ids);
        $project        = Project::where('id',$id)->with('users')->with('tasks')->first();
        $projectUsers   = $project->projectUser(); 
        $projectStatus  = Project::$projectStatus;
        // For Task
        $stages         = ProjectStage::where('created_by', '=', \Auth::user()->creatorId())->with('allTask')->get();

        $milestones = ProjectMilestone::where('project_id', $id)->get();
        $notes      = ProjectNote::where('project_id', $id)->get();
        $files      = ProjectFile::where('project_id', $id)->get();
        $comments   = ProjectComment::where('project_id', $id)->where('parent', 0)->with(['commentUser','subComment'])->get();
        $feedbacks  = ProjectClientFeedback::where('project_id', $id)->where('parent', 0)->with(['feedbackUser','subFeedback'])->get();
        $timesheets = Timesheet::where('project_id', $id)->with(['users','tasks','projects'])->get();

        $future     = strtotime($project->due_date);
        $timefromdb = strtotime(date('Y-m-d'));
        $timeleft   = $future - $timefromdb;
        $daysleft   = round((($timeleft / 24) / 60) / 60);

        $totalExpense = Expense::where('project', $project->id)->sum('amount');
        $invoices     = Invoice::where('project', $id)->where('type', 'Project')->with('clients')->get();
        if (\Auth::user()->type == 'employee') {
            $tasks = $project->userTasks();
        } else {
            $tasks = $project->tasks;
        }

        $ganttTasks = [];
        foreach ($tasks as $task) {

            $tmp                 = [];
            $tmp['id']           = 'task_' . $task->id;
            $tmp['name']         = $task->title;
            $tmp['start']        = $task->start_date;
            $tmp['end']          = $task->due_date;
            $tmp['custom_class'] = strtolower($task->priority);
            $tmp['progress']     = 0;
            $tmp['extra']        = [
                'priority' => __($task->priority),
                'stage' => !empty($task->stages) ? $task->stages->name : '',

                'description' => $task->description,

                'duration' => Carbon::parse($task->start_date)->format('d M Y H:i A') . ' - ' . Carbon::parse($task->due_date)->format('d M Y H:i A'),
            ];
            $ganttTasks[]        = $tmp;
        }

        return view('project.show', compact('project','projectUsers', 'projectStatus', 'stages', 'milestones', 'notes', 'files', 'comments', 'feedbacks', 'timesheets', 'invoices', 'daysleft', 'totalExpense', 'ganttTasks', 'duration'));
    }


    public function edit($ids)
    {
        $id      = \Crypt::decrypt($ids);
        $project = Project::find($id);

        $employees = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'employee')->get()->pluck('name', 'id');
        $clients   = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'client')->get()->pluck('name', 'id');
        $clients->prepend('Select Client', '');
        $labels = Label::where('created_by', '=', \Auth::user()->creatorId())->get();
        $labels->prepend('Select Lead', '');
        $leads = Lead::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $leads->prepend('Select Lead', 0);
        $categories = Category::where('created_by', '=', \Auth::user()->creatorId())->where('type', 2)->get()->pluck('name', 'id');
        $categories->prepend('Select Category', '');

        $projectStatus = [
            'not_started' => __('Not Started'),
            'in_progress' => __('In Progress'),
            'on_hold' => __('On Hold'),
            'canceled' => __('Canceled'),
            'finished' => __('Finished'),
        ];

        return view('project.edit', compact('clients', 'labels', 'employees', 'leads', 'categories', 'projectStatus', 'project'));
    }


    public function update(Request $request, Project $project)
    {
        if (\Auth::user()->type == 'company') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'price' => 'required',
                    'start_date' => 'required',
                    'due_date' => 'required',
                    'status' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('project.index')->with('error', $messages->first());
            }

            if ($project->status != $request->status) {
                $status = 1;
            } else {
                $status = 0;
            }
            $old_status = $project->status;
            $project->title       = $request->title;
            $project->category    = $request->category;
            $project->price       = $request->price;
            $project->start_date  = $request->start_date;
            $project->due_date    = $request->due_date;
            $project->lead        = $request->lead;
            $project->client      = $request->client;
            $project->status      = $request->status;
            $project->description = $request->description;
            $project->save();

            if ($status == 1) {
                $settings  = Utility::settings();

                if (isset($settings['project_status_updated_notification']) && $settings['project_status_updated_notification'] == 1) {
                    $uArr = [
                        'project_name' => $project->title,
                        'status' => $project->status
                    ];
                    //  dd($uArr);
                    Utility::send_slack_msg('new_project_status', $uArr);
                }
                if (isset($settings['telegram_project_status_updated_notification']) && $settings['telegram_project_status_updated_notification'] == 1) {
                    $uArr = [
                        'project_name' => $project->title,
                        'status' => $project->status
                    ];
                    //  dd($uArr);
                    Utility::send_telegram_msg('new_project_status', $uArr);
                }
            }
            return redirect()->route('project.index')->with('success', __('Project successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(Project $project)
    {
        if (\Auth::user()->type == 'company') {
            Project::deleteProject($project->id);
            $project->delete();
            return redirect()->route('project.index')->with('success', __('Project deleted successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function projectUser($id)
    {
        $assign_user = ProjectUser::select('user_id')->where('project_id', $id)->get()->pluck('user_id');
        $employee    = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'employee')->whereNotIn('id', $assign_user)->get()->pluck('name', 'id');
        $employee->prepend('Select User', '');

        return view('project.userAdd', compact('employee', 'id'));
    }

    public function grid()
    {

        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client' || \Auth::user()->type == 'employee') {
            $user = \Auth::user();
            if (\Auth::user()->type == 'client') {
                $projects = Project::where('client', '=', $user->id)->with('tasks')->get();
            } elseif (\Auth::user()->type == 'employee') {
                $projects = Project::select('projects.*')->leftjoin('project_users', 'project_users.project_id', 'projects.id')->where('project_users.user_id', '=', $user->id)->with('tasks')->get();
            } else {
                $projects = Project::where('created_by', '=', $user->creatorId())->with('tasks')->get();
            }


            $projectStatus = [
                'not_started' => __('Not Started'),
                'in_progress' => __('In Progress'),
                'on_hold' => __('On Hold'),
                'canceled' => __('Canceled'),
                'finished' => __('Finished'),
            ];

            $defualtView         = new UserDefualtView();
            $defualtView->route  = \Request::route()->getName();
            $defualtView->module = 'project';
            $defualtView->view   = 'grid';
            User::userDefualtView($defualtView);

            $stage  = ProjectStage::where('created_by',\Auth::user()->creatorId())->orderBy('order', 'desc')->first();
            $stage_id = $stage->id;

            return view('project.grid', compact('projects', 'projectStatus','stage_id'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function addProjectUser(Request $request, $id)
    {
        if (\Auth::user()->type == 'company') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'user' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('project.show', \Crypt::encrypt($id))->with('error', $messages->first());
            }

            foreach ($request->user as $key => $user) {
                $projectUser             = new ProjectUser();
                $projectUser->user_id    = $user;
                $projectUser->project_id = $id;
                $projectUser->save();
            }

            return redirect()->route('project.show', \Crypt::encrypt($id))->with('success', __('User successfully added.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroyProjectUser($projectId, $userId)
    {
        if (\Auth::user()->type == 'company') {
            $projectUser = ProjectUser::where('project_id', $projectId)->where('user_id', $userId)->first();
            $projectUser->delete();

            return redirect()->route('project.show', \Crypt::encrypt($projectId))->with('success', __('User successfully deleted from project.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function changeStatus(Request $request, $id)
    {
        if (\Auth::user()->type == 'company') {
            $status         = Project::find($id);
            $status->status = $request->status;
            $status->save();

            if ($request->status == 'finished') {
                $client     = User::find($status->client);
                $user       = \Auth::user();
                $projectArr = [
                    'project_name' => $status->title,
                    'project_category' => !empty(Category::find($status->category)) ? Category::find($status->category)->name : '',
                    'project_price' => $user->priceFormat($status->price),
                    'project_client' => $client->name,
                    'project_start_date' => $user->dateFormat($status->start_date),
                    'project_due_date' => $user->dateFormat($status->due_date),
                    'project_lead' => !empty(Lead::find(!empty($request->lead) ? $request->lead : 0)) ? Lead::find(!empty($request->lead) ? $request->lead->subject : 0) : '',
                ];
                // Send Email
                $resp = Utility::sendEmailTemplate('project_finished', [$client->id => $client->email], $projectArr);


                //webhook
                $module = "New status";
                $webhook = Utility::webhookSetting($module);
                if ($webhook) {
                    $parameter = json_encode($status);

                    // 1 parameter is URL , 2  (status Data) parameter is data , 3 parameter is method
                    $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                    if ($status == true) {
                        return redirect()->back()->with('success', __('Status Successfully Created.'));
                    } else {
                        return redirect()->back()->with('error', __('Status Call Failed.'));
                    }
                }
                //end webhook
                return redirect()->back()->with('success', __('Project status successfully change.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }


            return redirect()->back()->with('success', __('Project status successfully change.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // For Project Task

    public function taskCreate($project_id)
    {
        $project  = Project::where('created_by', '=', \Auth::user()->creatorId())->where('projects.id', '=', $project_id)->first();
      
        $projects = Project::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('title', 'id');
        $projects->prepend('Select Project', '');
        $priority = [
            'low' => __('Low'),
            'medium' => __('Medium'),
            'high' => __('High'),
        ];

        if ($project_id == 0) {
            $milestones = [];
            $users      = [];
        } else {
            $usersArr = ProjectUser::where('project_id', '=', $project_id)->get();
            $users    = array();
            foreach ($usersArr as $user) {
                if (!empty($user->projectUsers)) {
                    $users[$user->projectUsers->id] = ($user->projectUsers->name . ' - ' . $user->projectUsers->email);
                }
            }
            
            $milestones = ProjectMilestone::where('project_id', '=', $project->id)->get()->pluck('title', 'id');
        }
        

        return view('project.taskCreate', compact('project', 'projects', 'priority', 'users', 'milestones', 'project_id'));
    }

    public function taskStore(Request $request, $projec_id)
    {
        // dd($request->all());
        if (\Auth::user()->type == 'company') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'priority' => 'required',
                    'assign_to' => 'required',
                    'start_date' => 'required',
                    'due_date' => 'required',
                    'hours' => 'required',
                    ]
                );
                
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    
                    return redirect()->back()->with('error', $messages->first());
                }
                
                $usr = \Auth::user();
                
                
                $post = $request->all();
                if ($usr->type != 'company') {
                    $post['assign_to'] = $usr->id;
                }
                
                if ($projec_id == 0) {
                    $post['project_id'] = $request->project;
                } else {
                    $post['project_id'] = $projec_id;
                }
                
                if ($request->milestone_id == '') {
                    $post['milestone_id'] = 0;
                }
                $post['created_by'] = $usr->creatorId();
                
                $post['stage'] = !empty(ProjectStage::where('created_by', '=', $usr->creatorId())->first()) ? ProjectStage::where('created_by', '=', $usr->creatorId())->first()->id : 0;
                
            $task = ProjectTask::create($post);

            if ($request->get('synchronize_type') == 'google_calender') {

                $type = 'task';
                $request1 = new GoogleEvent();
                $request1->title = $request->title;
                $request1->start_date = $request->start_date;
                $request1->end_date = $request->due_date;
                
                Utility::addCalendarData($request1, $type);
            }
            
            $task = ProjectTask::find($task->id);
            ProjectActivityLog::create(
                [
                    'user_id' => $usr->creatorId(),
                    'project_id' => ($projec_id == 0) ? $request->project : $projec_id,
                    'log_type' => 'Create Task',
                    'remark' => json_encode(['title' => $task->title]),
                ]
            );
            $task->hours = $request->hours;
            $task->save();
            $employee = User::find($task->assign_to);
            $user     = \Auth::user();
            $taskArr  = [
                'project' => !empty(Project::find($post['project_id'])) ? Project::find($post['project_id'])->title : '',
                'task_title' => $task->title,
                'task_priority' => Project::$priority[$task->priority],
                'task_start_date' => $user->dateFormat($task->start_date),
                'task_due_date' => $user->dateFormat($task->due_date),
                'task_stage' => !empty(ProjectStage::find($task->stage)) ? ProjectStage::find($task->stage)->name : '',
                'task_assign_user' => $employee->name,
                'task_description' => $task->description,
                
            ];

            // Send Email
            $resp = Utility::sendEmailTemplate('task_assigned', [$employee->id => $employee->email], $taskArr);
            
            
            if ($projec_id == 0) {
                $project_name = Project::where('created_by', '=', \Auth::user()->creatorId())->where('id', '=', $request->project)->first();
            } else {
                $project_name = Project::where('created_by', '=', \Auth::user()->creatorId())->where('id', '=', $projec_id)->first();
            }
            $settings  = Utility::settings();
            
            // if (isset($settings['task_create_notification']) && $settings['task_create_notification'] == 1) {
                //     $msg = $request->title . " " . __("of") . ' ' . $project_name->title . ' ' . __("created by") . ' ' . \Auth::user()->name . '.';
                //     Utility::send_slack_msg($msg);
                // }
                if (isset($settings['task_create_notification']) && $settings['task_create_notification'] == 1) {
                $uArr = [
                    'user_name' => \Auth::user()->name,
                    'task_name' => $task->title,
                    'project_name' => $project_name->title,
                ];
                // dd($uArr);
                Utility::send_slack_msg('new_task', $uArr);
            }
            if (isset($settings['telegram_task_create_notification']) && $settings['telegram_task_create_notification'] == 1) {
                $uArr = [
                    'user_name' => \Auth::user()->name,
                    'task_name' => $task->title,
                    'project_name' => $project_name->title,
                ];
                // dd($uArr);
                Utility::send_telegram_msg('new_task', $uArr);
            }
            if (isset($settings['twilio_task_create_notification']) && $settings['twilio_task_create_notification'] == 1) {
                $uArr = [
                    'user_name' => \Auth::user()->name,
                    'task_name' => $task->title,
                    'project_name' => $project_name->title,
                ];
                // dd($uArr);
                Utility::send_twilio_msg('new_task', $uArr);
            }

            //webhook
            $module = "New Task";
            $webhook = Utility::webhookSetting($module);
            if ($webhook) {
                $parameter = json_encode($task);

                // 1 parameter is URL , 2  (Lead Data) parameter is data , 3 parameter is method
                $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                if ($status == true) {
                    return redirect()->back()->with('success', __('Lead successfully created.'));
                } else {
                    return redirect()->back()->with('error', __('Lead call failed.'));
                }
            }
            return redirect()->back()->with('success', __('Task successfully created.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    
    public function taskEdit($task_id)
    {
        $task     = ProjectTask::find($task_id);
        $project  = Project::where('created_by', '=', \Auth::user()->creatorId())->where('projects.id', '=', $task->project_id)->first();
        $projects = Project::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $usersArr = ProjectUser::where('project_id', '=', $task->project_id)->get();
        $priority = [
            'low' => __('Low'),
            'medium' => __('Medium'),
            'high' => __('High'),
        ];
        
        $milestones = [];
        if (!empty($project)) {
            $milestones = ProjectMilestone::where('project_id', '=', $project->id)->get()->pluck('title', 'id');
        }
        $users      = array();
        foreach ($usersArr as $user) {
            if (!empty($user->projectUsers)) {
                $users[$user->projectUsers->id] = ($user->projectUsers->name . ' - ' . $user->projectUsers->email);
            }
        }

        
        return view('project.taskEdit', compact('project', 'projects', 'users', 'task', 'priority', 'milestones'));
    }
    
    public function taskUpdate(Request $request, $task_id)
    {
        
        if (\Auth::user()->type == 'company') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'priority' => 'required',
                    'assign_to' => 'required',
                    'due_date' => 'required',
                    'start_date' => 'required',
                    // 'milestone_id' => 'required',
                    'hours' => 'required',
                    ]
                );
            }
            
            $task    = ProjectTask::find($task_id);
            $project = Project::where('created_by', '=', \Auth::user()->creatorId())->where('projects.id', '=', $task->project_id)->first();
            if ($project) {
                $post               = $request->all();
                $post['project_id'] = $task->project_id;
                $task->update($post);
                
                return redirect()->back()->with('success', __('Task Updated Successfully.'));
            } else {
                return redirect()->back()->with('error', __('You can \'t Edit Task!'));
        }
    }

    public function taskDestroy($task_id)
    {
        if (\Auth::user()->type == 'company') {
            $task    = ProjectTask::find($task_id);
            $project = Project::find($task->project_id);
            if ($project->created_by == \Auth::user()->creatorId()) {
                $task->delete();
                
                return redirect()->back()->with('success', __('Task successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('You can\'t Delete Task.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function order(Request $request)
    {
        $post  = $request->all();
        $task  = ProjectTask::find($post['task_id']);
        $old_stage_name = ProjectStage::where('created_by', '=', \Auth::user()->creatorId())->where('id', '=', $task->stage)->first();
        $task->stage = $post['stage_id'];
        $task->save();
        $new_stage_name = ProjectStage::where('created_by', '=', \Auth::user()->creatorId())->where('id', '=', $task->stage)->first();
        $settings  = Utility::settings();
        
        if (isset($settings['task_move_notification']) && $settings['task_move_notification'] == 1) {
            $uArr = [
                'task_title' => $task->title,
                'task_stage' => $old_stage_name->name,
                'new_task_stage' => $new_stage_name->name,
            ];
            Utility::send_slack_msg('task_moved', $uArr);
        }
        
        if (isset($settings['telegram_task_move_notification']) && $settings['telegram_task_move_notification'] == 1) {
            $uArr = [
                'task_title' => $task->title,
                'task_stage' => $old_stage_name->name,
                'new_task_stage' => $new_stage_name->name,
            ];
            Utility::send_telegram_msg('task_moved', $uArr);
        }

        // if (isset($settings['telegram_task_move_notification']) && $settings['telegram_task_move_notification'] == 1) {
            //     $resp = $task->title . __(' stage changed from ') . $old_stage_name->name . __(' to ') . $new_stage_name->name . '.';
            //     Utility::send_telegram_msg($resp);
        // }
    }

    public function taskShow($task_id, $client_id = '')
    {

        $task    = ProjectTask::find($task_id);

        $project = Project::find($task->project_id);

        $userTask = ProjectTask::where('assign_to', \Auth::user()->id)->where('time_tracking', 1)->first();
        $lastTime = [];
        if (!empty($userTask)) {
            $lastTime = ProjectTaskTimer::where('task_id', $userTask->id)->orderBy('id', 'desc')->first();
        }


        return view('project.taskShow', compact('task', 'lastTime'));
    }

    public function checkListStore(Request $request, $task_id)
    {
        $usr = \Auth::user();
        if ($usr->type == 'company' || $usr->type == 'employee') {
            $request->validate(['name' => 'required']);

            $post['task_id']        =    $task_id;
            $post['name']           =    $request->name;
            $post['created_by']     =    $usr->creatorId();
            $CheckList              =    ProjectTaskCheckList::create($post);
            $CheckList->deleteUrl   =    route('project.task.checklist.destroy', [$CheckList->task_id, $CheckList->id,]);
            $CheckList->updateUrl   =    route('project.task.checklist.update', [$CheckList->task_id, $CheckList->id,]);

            return $CheckList->toJson();
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function checklistDestroy(Request $request, $task_id, $checklist_id)
    {
        if (\Auth::user()->type == 'company') {
            $checklist = ProjectTaskCheckList::find($checklist_id);
            $checklist->delete();

            return "true";
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function checklistUpdate($task_id, $checklist_id)
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee') {
            $checkList = ProjectTaskCheckList::find($checklist_id);
            if ($checkList->status == 0) {
                $checkList->status = 1;
            } else {
                $checkList->status = 0;
            }
            $checkList->save();

            return $checkList->toJson();
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function commentStore(Request $request, $project_id, $task_id)
    {

        $post               = [];
        $post['task_id']    = $task_id;
        $post['comment']    = $request->comment;
        $post['created_by'] = \Auth::user()->authId();
        $post['user_type']  = \Auth::user()->type;
        $comment            = ProjectTaskComment::create($post);

        $comment->deleteUrl = route('project.task.comment.destroy', [$comment->id]);

        $task    = ProjectTask::find($task_id);
        $settings  = Utility::settings();
        // if (isset($settings['task_comment_notification']) && $settings['task_comment_notification'] == 1) {
        //     $msg = __('comment added in ') . $task->title . '.';
        //     Utility::send_slack_msg($msg);
        // }

        if (isset($settings['task_comment_notification']) && $settings['task_comment_notification'] == 1) {
            $uArr = [
                'user_name' => \Auth::user()->name,
                'task_name' => $task->title,
                'project_name' =>   $task->project_name,
            ];
            Utility::send_slack_msg('new_task_comment', $uArr);
        }
        if (isset($settings['telegram_task_comment_notification']) && $settings['telegram_task_comment_notification'] == 1) {
            $uArr = [
                'user_name' => \Auth::user()->name,
                'task_name' => $task->title,
                'project_name' =>   $task->project_name,
            ];
            Utility::send_telegram_msg('new_task_comment', $uArr);
        }

        // if (isset($settings['telegram_task_comment_notification']) && $settings['telegram_task_comment_notification'] == 1) {
        //     $resp = __('comment added in ') . $task->title . '.';
        //     Utility::send_telegram_msg($resp);
        // }

        return $comment->toJson();
    }

    public function commentDestroy($comment_id)
    {
        $comment = ProjectTaskComment::find($comment_id);
        $comment->delete();

        return "true";
    }


    public function commentStoreFile(Request $request, $task_id)
    {
        //storage limit
        $image_size = $request->file('file')->getSize();
        $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);
        if ($result == 1) {
            $file_name = $request->file->getClientOriginalName();
            $fileName = $task_id . time() . "_" . $request->file->getClientOriginalName();
            $settings = Utility::getStorageSetting();
            // $url = '';
            $dir        = 'uploads/tasks/';
            $path = Utility::upload_file($request, 'file', $fileName, $dir, []);

            // dd($path);
            if ($path['flag'] == 1) {
                $url = $path['url'];
            } else {
                return redirect()->route('project.task', \Auth::user()->id)->with('error', __($path['msg']));
            }
            // $request->file->storeAs('uploads/tasks', $fileName);
            $post['task_id']    = $task_id;

            $post['file']       = $fileName;
            $post['name']       = $request->file->getClientOriginalName();
            $post['extension']  = "." . $request->file->getClientOriginalExtension();
            $post['file_size']  = $request->file;

            $post['created_by'] = \Auth::user()->creatorId();
            $post['user_type']  = \Auth::user()->type;

            $TaskFile            = ProjectTaskFile::create($post);
            $TaskFile->deleteUrl = route('project.task.comment.file.destroy', [$TaskFile->id]);
            // dd( $TaskFile->deleteUrl);

            return $TaskFile->toJson();
        }
    }


    public function commentDestroyFile(Request $request, $file_id)
    {
        $commentFile = ProjectTaskFile::find($file_id);
        $path        = storage_path('uploads/tasks/' . $commentFile->file);
        if (file_exists($path)) {
            \File::delete($path);
        }
        $commentFile->delete();

        return "true";
    }

    public function milestone($project_id)
    {
        $project = Project::find($project_id);
        $status  = Project::$status;

        return view('project.milestoneCreate', compact('project', 'status'));
    }

    public function milestoneStore(Request $request, $project_id)
    {
        if (\Auth::user()->type == 'company') {
            $project = Project::find($project_id);

            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'status' => 'required',
                    'cost' => 'required',
                    'due_date' => 'required',
                    'start_date' => 'required'
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('project.show', \Crypt::encrypt($project_id))->with('error', $messages->first());
            }

            $milestone              = new ProjectMilestone();
            $milestone->project_id  = $project->id;
            $milestone->title       = $request->title;
            $milestone->status      = $request->status;
            $milestone->cost        = $request->cost;
            $milestone->start_date    = $request->start_date;
            $milestone->due_date    = $request->due_date;
            $milestone->description = $request->description;
            $milestone->save();

            ProjectActivityLog::create(
                [
                    'user_id' => \Auth::user()->creatorId(),
                    'project_id' => $project->id,
                    'log_type' => 'Create Milestone',
                    'remark' => json_encode(['title' => $milestone->title]),
                ]
            );
            $settings  = Utility::settings();
            // if (isset($settings['milestone_create_notification']) && $settings['milestone_create_notification'] == 1) {
            //     $msg = __('New Milestone ') . $request->title . __(' created for ') . $project->title . '.';
            //     //dd($msg);
            //     Utility::send_slack_msg($msg);
            // }
            if (isset($settings['milestone_create_notification']) && $settings['milestone_create_notification'] == 1) {
                $uArr = [
                    'user_name' => \Auth::user()->name,
                    'title' => $milestone->title,
                    'cost' => $milestone->cost,
                    'start_date' => $milestone->start_date,
                    'due_date' => $milestone->due_date,
                ]; // dd($uArr);
                Utility::send_slack_msg('new_milestone', $uArr);
            }
            if (isset($settings['telegram_milestone_create_notification']) && $settings['telegram_milestone_create_notification'] == 1) {
                $uArr = [
                    'user_name' => \Auth::user()->name,
                    'title' => $milestone->title,
                    'cost' => $milestone->cost,
                    'start_date' => $milestone->start_date,
                    'due_date' => $milestone->due_date,
                ]; // dd($uArr);
                Utility::send_telegram_msg('new_milestone', $uArr);
            }

            // if (isset($settings['telegram_milestone_create_notification']) && $settings['telegram_milestone_create_notification'] == 1) {
            //     $resp = __('New Milestone ') . $request->title . __(' created for ') . $project->title . '.';
            //     Utility::send_telegram_msg($resp);
            // }
            //webhook
            $module = "New milestone";
            $webhook = Utility::webhookSetting($module);
            if ($webhook) {
                $parameter = json_encode($milestone);

                // 1 parameter is URL , 2  (milestone Data) parameter is data , 3 parameter is method
                $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                if ($status == true) {
                    return redirect()->back()->with('success', __('milestone successfully created.'));
                } else {
                    return redirect()->back()->with('error', __('milestone call failed.'));
                }
            }
            return redirect()->back()->with('success', __('Milestone successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function milestoneEdit($id)
    {
        $milestone = ProjectMilestone::find($id);
        $status    = Project::$status;

        return view('project.milestoneEdit', compact('milestone', 'status'));
    }

    public function milestoneUpdate($id, Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $milestone = ProjectMilestone::find($id);
            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'status' => 'required',
                    'cost' => 'required',
                    'due_date' => 'required',
                    'start_date' => 'required'
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('project.show', \Crypt::encrypt($milestone->project_id))->with('error', $messages->first());
            }


            $milestone->title       = $request->title;
            $milestone->status      = $request->status;
            $milestone->cost        = $request->cost;
            $milestone->progress    = $request->progress;
            $milestone->due_date    = $request->due_date;
            $milestone->start_date  = $request->start_date;
            $milestone->description = $request->description;
            $milestone->save();
            return redirect()->back()->with('success', __('Milestone successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function milestoneDestroy($id)
    {

        $milestone = ProjectMilestone::find($id);
        $milestone->delete();

        return redirect()->back()->with('success', __('Milestone successfully deleted.'));
    }


    public function notes($project_id)
    {
        $project = Project::find($project_id);

        return view('project.noteCreate', compact('project'));
    }

    public function noteStore(Request $request, $project_id)
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client') {
            $project = Project::find($project_id);

            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'description' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('project.show', \Crypt::encrypt($project_id))->with('error', $messages->first());
            }

            $notes              = new ProjectNote();
            $notes->project_id  = $project->id;
            $notes->title       = $request->title;
            $notes->description = $request->description;
            $notes->created_by  = $project->created_by;
            $notes->save();

            ProjectActivityLog::create(
                [
                    'user_id' => \Auth::user()->creatorId(),
                    'project_id' => $project->id,
                    'log_type' => 'Create Notes',
                    'remark' => json_encode(['title' => $notes->title]),
                ]
            );


            return redirect()->back()->with('success', __('Notes successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function noteEdit($project_id, $note_id)
    {
        $note = ProjectNote::find($note_id);

        return view('project.noteEdit', compact('note', 'project_id'));
    }

    public function noteUpdate(Request $request, $project_id, $note_id)
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'description' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('project.show', \Crypt::encrypt($project_id))->with('error', $messages->first());
            }


            $notes              = ProjectNote::find($note_id);
            $notes->title       = $request->title;
            $notes->description = $request->description;
            $notes->save();


            return redirect()->back()->with('success', __('Notes successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function noteDestroy($project_id, $id)
    {
        if (\Auth::user()->type == 'company') {
            $note = ProjectNote::find($id);
            $note->delete();

            return redirect()->back()->with('success', __('Note successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function file($project_id)
    {
        $project = Project::find($project_id);
        return view('project.fileCreate', compact('project'));
    }

    public function fileStore(Request $request, $project_id)
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client') {
            $project = Project::find($project_id);

            $validator = \Validator::make(
                $request->all(),
                [
                    'file' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('project.show', \Crypt::encrypt($project_id))->with('error', $messages->first());
            }
            //storage limit
            $image_size = $request->file('file')->getSize();
            $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);
            if ($result == 1) {

                $file_name = $request->file->getClientOriginalName();
                $fileName = $project_id . time() . "_" . $request->file->getClientOriginalName();
                $settings = Utility::getStorageSetting();
                $url = '';
                $dir        = 'uploads/files/';

                $path = Utility::upload_file($request, 'file', $fileName, $dir, []);

                if ($path['flag'] == 1) {
                    $url = $path['url'];
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }

                $notes              = new ProjectFile();
                $notes->project_id  = $project->id;
                $notes->file        = $fileName;
                $notes->description = $request->description;
                $notes->save();
            }
            ProjectActivityLog::create(
                [
                    'user_id' => \Auth::user()->creatorId(),
                    'project_id' => $project->id,
                    'log_type' => 'Uploads Files',
                    'remark' => json_encode(['title' => 'Project file uploads']),
                ]
            );

            return redirect()->back()->with('success', __('File successfully created.') . ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : ''));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function fileEdit($project_id, $note_id)
    {
        $file = ProjectFile::find($note_id);

        return view('project.fileEdit', compact('file', 'project_id'));
    }

    public function fileUpdate(Request $request, $project_id, $file_id)
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client') {

            $file = ProjectFile::find($file_id);

            if (!empty($request->file)) {
                //storage limit
                $file_path = 'uploads/files/' . $file->file;
                $image_size = $request->file('file')->getSize();
                $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);
                if ($result == 1) {
                    Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);
                    $file_name = $request->file->getClientOriginalName();
                    $fileName = $project_id . time() . "_" . $request->file->getClientOriginalName();
                    $settings = Utility::getStorageSetting();
                    $url = '';
                    $dir        = 'uploads/files/';
                    $path = Utility::upload_file($request, 'file', $fileName, $dir, []);

                    if ($path['flag'] == 1) {
                        $url = $path['url'];
                    } else {
                        return redirect()->back()->with('error', __($path['msg']));
                    }
                    $file->file = $fileName;
                }
            }

            $file->description = $request->description;
            $file->save();

            return redirect()->back()->with('success', __('File successfully updated.') . ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : ''));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function fileDestroy($project_id, $id)
    {
        $file = ProjectFile::find($id);

        //storage limit
        $file_path = 'uploads/files/' . $file->file;
        $file->delete();
        Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);
        return redirect()->back()->with('success', __('File successfully deleted.'));
    }


    public function projectCommentStore(Request $request, $project_id)
    {
        $project = Project::find($project_id);
        $validator = \Validator::make(
            $request->all(),
            [
                'comment' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->route('project.show', \Crypt::encrypt($project_id))->with('error', $messages->first());
        }

        if (!empty($request->file)) {
            $image_size = $request->file('file')->getSize();
            $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);
            if ($result == 1) {
                $file_name = $request->file->getClientOriginalName();
                $fileName =  time() . "_" . $request->file->getClientOriginalName();
                $settings = Utility::getStorageSetting();
                $url = '';
                $dir        = 'uploads/files/';
                $path = Utility::upload_file($request, 'file', $fileName, $dir, []);

                if ($path['flag'] == 1) {
                    $url = $path['url'];
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }
            }
        }

        $comments             = new ProjectComment();
        $comments->project_id = $project->id;
        $comments->file       = !empty($fileName) ? $fileName : '';
        $comments->comment    = $request->comment;
        $comments->comment_by = \Auth::user()->id;
        $comments->parent     = !empty($request->parent) ? $request->parent : 0;
        $comments->save();

        ProjectActivityLog::create(
            [
                'user_id' => \Auth::user()->creatorId(),
                'project_id' => $project->id,
                'log_type' => 'Comment Create',
                'remark' => json_encode(['title' => 'Project comment Post']),
            ]
        );


        return redirect()->back()->with('success', __('Comment successfully posted.'));
    }

    public function projectCommentReply($project_id, $comment_id)
    {
        return view('project.commentReply', compact('project_id', 'comment_id'));
    }


    public function projectClientFeedbackStore(Request $request, $project_id)
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client') {
            $project = Project::find($project_id);

            $validator = \Validator::make(
                $request->all(),
                [
                    'feedback' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->route('project.show', \Crypt::encrypt($project_id))->with('error', $messages->first());
            }

            if (!empty($request->file)) {
                $image_size = $request->file('file')->getSize();
                $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);
                if ($result == 1) {
                    $file_name = $request->file->getClientOriginalName();
                    $fileName =  time() . "_" . $request->file->getClientOriginalName();
                    $settings = Utility::getStorageSetting();
                    $url = '';
                    $dir        = 'uploads/files/';
                    $path = Utility::upload_file($request, 'file', $fileName, $dir, []);

                    if ($path['flag'] == 1) {
                        $url = $path['url'];
                    } else {
                        return redirect()->back()->with('error', __($path['msg']));
                    }
                }
            }


            $feedback              = new ProjectClientFeedback();
            $feedback->project_id  = $project->id;
            $feedback->file        = !empty($fileName) ? $fileName : '';
            $feedback->feedback    = $request->feedback;
            $feedback->feedback_by = \Auth::user()->id;
            $feedback->parent      = !empty($request->parent) ? $request->parent : 0;
            $feedback->save();

            ProjectActivityLog::create(
                [
                    'user_id' => \Auth::user()->creatorId(),
                    'project_id' => $project->id,
                    'log_type' => 'Feedback Create',
                    'remark' => json_encode(['title' => 'Project comment post']),
                ]
            );


            return redirect()->back()->with('success', __('Feedback successfully posted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function projectClientFeedbackReply($project_id, $comment_id)
    {

        return view('project.clientFeedbackReply', compact('project_id', 'comment_id'));
    }

    public function projectTimesheet($project_id)
    {
        $project = Project::find($project_id);
        if ($project_id == 0) {
            $users = [];
            $tasks = [];
        } else {
            $users = $project->projectUser();
            $tasks = $project->tasks;
        }

        $projectList = Project::where('created_by', \Auth::user()->creatorId())->get()->pluck('title', 'id');
        $projectList->prepend('Select Project', '');

        return view('project.timesheetCreate', compact('project', 'users', 'tasks', 'project_id', 'projectList'));
    }

    public function projectTimesheetStore(Request $request, $project_id)
    {

        if (\Auth::user()->type == 'company') {
            $project = Project::find($project_id);

            $validator = \Validator::make(
                $request->all(),
                [
                    'employee' => 'required',
                    'start_date' => 'required',
                    'start_time' => 'required',
                    'end_date' => 'required',
                    'end_time' => 'required',
                ]
            );

            if ($project_id == 0) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'project' => 'required',
                    ]
                );
            }
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $timesheet = new Timesheet();
            if ($project_id == 0) {
                $timesheet->project_id = $request->project;
                $timesheet->task_id    = !empty($request->task) ? $request->task : 0;
            } else {
                $timesheet->project_id = $project->id;
                $timesheet->task_id    = $request->task_id;
            }

            $timesheet->employee   = $request->employee;
            $timesheet->start_date = $request->start_date;
            $timesheet->start_time = $request->start_time;
            $timesheet->end_date   = $request->end_date;
            $timesheet->end_time   = $request->end_time;

            $timesheet->notes      = $request->notes;
            $timesheet->created_by = \Auth::user()->creatorId();
            $timesheet->save();

            ProjectActivityLog::create(
                [
                    'user_id' => \Auth::user()->creatorId(),
                    'project_id' => ($project_id == 0) ? $request->project : $project->id,
                    'log_type' => 'Create Timesheet',
                    'remark' => json_encode(['title' => $timesheet->notes]),
                ]
            );


            return redirect()->back()->with('success', __('Timesheet successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function projectTimesheetEdit($project_id, $id)
    {
        $timesheet = Timesheet::find($id);
        $project   = Project::find($project_id);
        $users     = $project->projectUser();
        $tasks     = $project->tasks;

        return view('project.timesheetEdit', compact('project', 'users', 'tasks', 'timesheet'));
    }


    public function projectTimesheetUpdate(Request $request, $project_id, $id)
    {
        if (\Auth::user()->type == 'company') {
            $project = Project::find($project_id);

            $validator = \Validator::make(
                $request->all(),
                [
                    'employee' => 'required',
                    'start_date' => 'required',
                    'start_time' => 'required',
                    'end_date' => 'required',
                    'end_time' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('project.show', \Crypt::encrypt($project_id))->with('error', $messages->first());
            }

            $timesheet             = Timesheet::find($id);
            $timesheet->project_id = $project->id;
            $timesheet->employee   = $request->employee;
            $timesheet->start_date = $request->start_date;
            $timesheet->start_time = $request->start_time;
            $timesheet->end_date   = $request->end_date;
            $timesheet->end_time   = $request->end_time;
            $timesheet->task_id    = $request->task_id;
            $timesheet->notes      = $request->notes;
            $timesheet->save();


            return redirect()->back()->with('success', __('Timesheet successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function projectTimesheetNote($project_id, $id)
    {
        $timesheet = Timesheet::find($id);

        return view('project.timesheetNote', compact('timesheet'));
    }

    public function projectTimesheetDestroy($project_id, $id)
    {

        if (\Auth::user()->type == 'company') {
            $timesheet = Timesheet::find($id);
            $timesheet->delete();

            return redirect()->back()->with('success', __('Timesheet successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    //For All Project Task

    public function allTask(Request $request)
    {
        $priority  = [
            'low' => __('Low'),
            'medium' => __('Medium'),
            'high' => __('High'),
        ];
        $stageList = ProjectStage::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

        $stages = ProjectStage::where('created_by', '=', \Auth::user()->creatorId())->orderBy('order', 'ASC');

        if (!empty($request->status)) {
            $stages->where('id', $request->status);
        }
        $stages = $stages->get();

        if (\Auth::user()->type == 'company') {
            $projects = $projectList = Project::where('created_by', \Auth::user()->creatorId());
        } elseif (\Auth::user()->type == 'employee') {
            $projects = $projectList = Project::select('projects.*', 'project_users.user_id')->leftJoin(
                'project_users',
                function ($join) {
                    $join->on('projects.id', '=', 'project_users.project_id');
                    $join->where('project_users.user_id', \Auth::user()->id);
                }
            )->where('created_by', \Auth::user()->creatorId());
        } else {
            $projects = $projectList = Project::where('client', \Auth::user()->id);
        }

        if (!empty($request->project)) {
            $projects->where('id', $request->project);
        }

        $projectList = $projects->pluck('title', 'id');
        $projectList->prepend('Select Project', '');
        $projects = $projects->with('tasks')->get();

        // $projectList = Project::where('created_by', \Auth::user()->creatorId())->get()->pluck('title', 'id');
        // $projectList->prepend('Select Project', '');
        // $projects = $projects->with('tasks')->get();

        $defualtView         = new UserDefualtView();
        $defualtView->route  = \Request::route()->getName();
        $defualtView->module = 'All Task';
        $defualtView->view   = 'list';
        User::userDefualtView($defualtView);

        return view('project.allTask', compact('stages', 'projects', 'projectList', 'priority', 'stageList'));
    }
    public function allTaskKanban(Request $request)
    {
        $priority  = Project::$priority;
        $stageList = ProjectStage::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

        $stages = ProjectStage::where('created_by', '=', \Auth::user()->creatorId());

        if (!empty($request->status)) {
            $stages->where('id', $request->status);
        }
        $stages = $stages->get();

        if (\Auth::user()->type == 'company') {
            $projects = $projectList = Project::where('created_by', \Auth::user()->creatorId());
        } elseif (\Auth::user()->type == 'employee') {
            $projects = $projectList = Project::select('projects.*', 'project_users.user_id')->leftJoin(
                'project_users',
                function ($join) {
                    $join->on('projects.id', '=', 'project_users.project_id');
                    $join->where('project_users.user_id', \Auth::user()->id);
                }
            )->where('created_by', \Auth::user()->creatorId());
        } else {
            $projects = $projectList = Project::where('client', \Auth::user()->id);
        }

        if (!empty($request->project)) {
            $projects->where('id', $request->project);
        }

        $projectList = Project::where('created_by', \Auth::user()->creatorId())->get()->pluck('title', 'id');
        $projectList->prepend('Select Project', '');

        $projects            = $projects->with('tasks')->get();
        $defualtView         = new UserDefualtView();
        $defualtView->route  = \Request::route()->getName();
        $defualtView->module = 'all task';
        $defualtView->view   = 'kanban';
        User::userDefualtView($defualtView);

        return view('project.allTaskKanban', compact('stages', 'projects', 'projectList', 'priority', 'stageList'));
    }

    public function allTaskGanttChart(Request $request, $duration = 'Week')
    {
        if (\Auth::user()->type == 'company') {
            $projects = Project::where('created_by', \Auth::user()->creatorId())->with('tasks');
        } elseif (\Auth::user()->type == 'employee') {
            $projects = Project::select('projects.*', 'project_users.user_id')->with('tasks')->leftJoin(
                'project_users',
                function ($join) {
                    $join->on('projects.id', '=', 'project_users.project_id');
                    $join->where('project_users.user_id', \Auth::user()->id);
                }
            )->where('created_by', \Auth::user()->creatorId());
        } else {
            $projects = Project::where('client', \Auth::user()->id)->with('tasks');
        }
        if (!empty($request->project)) {
            $projects->where('id', $request->project);
        }
        $projects = $projects->get();

        $tasksArray = [];
        foreach ($projects as $project) {
            if (\Auth::user()->type == 'employee') {
                $task = $project->userTasks();
            } else {
                $task = $project->tasks;
            }
            $tasksArray[] = $task;
        }

        $tasks = [];
        array_walk(
            $tasksArray,
            function ($item, $key) use (&$tasks) {
                foreach ($item as $value)
                    $tasks[] = $value;
            }
        );

        $ganttTasks = [];

        foreach ($tasks as $task) {
            $tmp                 = [];
            $tmp['id']           = 'task_' . $task->id;
            $tmp['name']         = $task->title;
            $tmp['start']        = $task->start_date;
            $tmp['end']          = $task->due_date;
            $tmp['custom_class'] = strtolower($task->priority);
            $tmp['progress']     = 0;
            $tmp['extra']        = [
                'priority' => __($task->priority),
                'stage' => !empty($task->stages) ? $task->stages->name : '',
                'description' => $task->description,
                'duration' => Carbon::parse($task->start_date)->format('d M Y H:i A') . ' - ' . Carbon::parse($task->due_date)->format('d M Y H:i A'),
            ];
            $ganttTasks[]        = $tmp;
        }


        return view('project.allTaskGanttChart', compact('projects', 'ganttTasks', 'duration'));
    }

    public function getMilestone(Request $request)
    {
        $milestones = ProjectMilestone::where('project_id', $request->project_id)->get()->pluck('title', 'id');

        return response()->json($milestones);
    }

    public function getUser(Request $request)
    {
        $usersArr = ProjectUser::orderBy('id');
        if (!empty($request->project_id)) {
            $usersArr->where('project_id', '=', $request->project_id);
        }
        $usersArr = $usersArr->get();
        $users    = array();
        foreach ($usersArr as $user) {
            $users[!empty($user->projectUsers) ? $user->projectUsers->id : ''] =
                !empty($user->projectUsers) ? $user->projectUsers->name . ' - ' . $user->projectUsers->email  : '-';
        }

        return response()->json($users);
    }

    //    For All Project Task

    public function allTimesheet(Request $request)
    {

        if (\Auth::user()->type == 'company') {
            $projectList = Project::where('created_by', \Auth::user()->creatorId());
        } elseif (\Auth::user()->type == 'employee') {
            $projectList = Project::select('projects.*', 'project_users.user_id')->leftJoin(
                'project_users',
                function ($join) {
                    $join->on('projects.id', '=', 'project_users.project_id');
                    $join->where('project_users.user_id', \Auth::user()->id);
                }
            )->where('created_by', \Auth::user()->creatorId());
        }

        if (\Auth::user()->type == 'company') {
            $timesheet = Timesheet::where('created_by', \Auth::user()->creatorId());
        } else {
            $timesheet = Timesheet::where('employee', \Auth::user()->id);
        }


        if (!empty($request->project)) {
            $timesheet->where('project_id', $request->project);
        }
        if (!empty($request->task)) {
            $timesheet->where('task_id', $request->task);
        }
        if (!empty($request->user)) {
            $timesheet->where('employee', $request->user);
        }
        if (!empty($request->start_date)) {
            $timesheet->where('start_date', '>=', $request->start_date);
        }
        if (!empty($request->end_date)) {
            $timesheet->where('end_date', '<=', $request->end_date);
        }

        $timesheet = $timesheet->with(['projects','users','tasks'])->get();

        $projectList = $projectList->get()->pluck('title', 'id');
        $projectList->prepend('All', '');

        return view('project.allTimesheet', compact('timesheet', 'projectList'));
    }

    public function getTask(Request $request)
    {
        $tasks = ProjectTask::orderBy('id');
        if (!empty($request->project_id)) {
            $tasks->where('project_id', $request->project_id);
        }
        $tasks = $tasks->get()->pluck('title', 'id');

        return response()->json($tasks);
    }

    public function ganttPost($projectID, Request $request)
    {
        $id               = trim($request->task_id, 'task_');
        $task             = ProjectTask::find($id);
        $task->start_date = $request->start;
        $task->due_date   = $request->end;
        $task->save();

        return response()->json(
            [
                'is_success' => true,
                'message' => __("Time Updated"),
            ],
            200
        );
    }


    // Project  Task Timer
    public function taskShows($task_id, $client_id = '')
    {
        $task    = ProjectTask::find($task_id);
        // dd($task);
        $project = Project::find($task->project_id);

        $permissions = $project->client_project_permission();

        $perArr      = (!empty($permissions) ? explode(',', $permissions->permissions) : []);

        return view('project.taskShows', compact('task', 'perArr'));
    }

    public function taskStart(Request $request)
    {
        $type = $request->type;
        $id   = $request->id;
        $task = ProjectTask::find($id);

        if ($type == 'start') {
            if (\Auth::user()->type == 'employee') {
                $userTask = ProjectTask::where('assign_to', \Auth::user()->id)->where('time_tracking', 1)->first();
            } else {
                $userTask = ProjectTask::where('time_tracking', 1)->first();
            }

            if (!empty($userTask)) {
                $response['status'] = 'error';
                $response['msg']    = __('You are not start multiple tracker.');
                $response['class']  = 'Error';

                return \GuzzleHttp\json_encode($response);
            }

            $taskTimer             = new ProjectTaskTimer();
            $taskTimer->task_id    = $id;
            $taskTimer->start_time = date('Y-m-d G:i:s');

            $task->time_tracking    = 1;
            $msg                    = __('Now your task timer is start');
            $response['start_time'] = date('Y-m-d G:i:s');

            $timesheet             = new Timesheet();
            $timesheet->project_id = $task->project_id;
            $timesheet->task_id    = $task->id;
            $timesheet->employee   = \Auth::user()->id;
            $timesheet->start_date = date('Y-m-d');
            $timesheet->start_time = date('G:i:s');
            $timesheet->created_by = \Auth::user()->creatorId();
            $timesheet->save();
        } elseif ($type == 'stop') {

            $taskTimer           = ProjectTaskTimer::where('task_id', $id)->whereNotNull('start_time')->whereNull('end_time')->first();
            $taskTimer->end_time = date('Y-m-d G:i:s');
            $task->time_tracking = 0;
            $msg                 = __('Now your task timer is stop');

            $timesheet           = Timesheet::where('task_id', $id)->whereNull('end_date')->first();
            $timesheet->end_date = date('Y-m-d');
            $timesheet->end_time = date('G:i:s');
            $timesheet->save();
        }
        $taskTimer->save();
        $task->save();

        if (!empty($task)) {
            $response['status'] = 'success';
            $response['msg']    = $msg;
            $response['class']  = 'Success';
        } else {
            $response['status'] = 'error';
            $response['msg']    = __('Something went wrong');
            $response['class']  = 'Error';
        }


        return \GuzzleHttp\json_encode($response);
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
            if (isset($arrParam['workspace_id'])) {
                $objProject->whereIn(
                    'project_id',
                    function ($query) use ($arrParam) {
                        $query->select('id')->from('projects')->where('workspace', '=', $arrParam['workspace_id']);
                    }
                );
            }
            $data = $objProject->pluck('total', 'status')->all();
            $arrTask['label'][] = __($label);
        }

        return $arrTask;
    }

    public function copyproject($id)
    {

        $project = Project::find($id);
        return view('project.copy', compact('project'));
    }

    public function copyprojectstore(Request $request, $id)
    {
        $project                          = Project::find($id);
        $duplicate                           = new Project();
        $duplicate['title']                  = $project->title;
        $duplicate['category']               = $project->category;
        $duplicate['price']                  = $project->price;
        $duplicate['description']            = $project->description;
        $duplicate['client']                 = $project->client;
        $duplicate['start_date']             = $project->start_date;
        $duplicate['due_date']               = $project->due_date;
        $duplicate['description']            = $project->description;
        $duplicate['label']                  = $project->label;
        $duplicate['lead']                   = $project->lead;
        $duplicate['status']                 = $project->status;
        $duplicate['created_by']             = \Auth::user()->creatorId();
        $duplicate->save();

        if (isset($request->notes) && in_array("notes", $request->notes)) {
            $notes = ProjectNote::where('project_id', $project->id)->get();
            foreach ($notes as $note) {
                $Notes                = new ProjectNote();
                $Notes['project_id']  = $duplicate->id;
                $Notes['title']   = $note->title;
                $Notes['description']   = $note->description;
                $Notes->save();
            }
        }

        if (isset($request->comments) && in_array("comments", $request->comments)) {
            $comments = ProjectComment::where('project_id', $project->id)->get();
            foreach ($comments as $comment) {

                $Comments                = new ProjectComment();
                $Comments['project_id']  = $duplicate->id;
                $Comments['file']   = $comment->file;
                $Comments['comment']   = $comment->comment;
                $Comments->save();
            }
        }

        if (isset($request->user) && in_array("user", $request->user)) {
            $users = ProjectUser::where('project_id', $project->id)->get();
            foreach ($users as $user) {
                $users = new ProjectUser();
                $users['user_id'] = $user->user_id;
                $users['project_id'] = $duplicate->id;
                $users->save();
            }
        } else {
            $objUser = Auth::user();
            $users              = new ProjectUser();
            $users['user_id']   = $objUser->id;
            $users['project_id'] = $duplicate->id;
            $users->save();
        }

        if (isset($request->task) && in_array("task", $request->task)) {
            $tasks = ProjectTask::where('project_id', $project->id)->get();
            foreach ($tasks as $task) {
                $project_task                   = new ProjectTask();
                $project_task['title']          = $task->title;
                $project_task['priority']       = $task->priority;
                $project_task['description']    = $task->description;
                $project_task['due_date']       = $task->due_date;
                $project_task['start_date']     = $task->start_date;
                $project_task['hours']          = $task->hours;
                $project_task['assign_to']      = $task->assign_to;
                $project_task['project_id']     = $duplicate->id;
                $project_task['milestone_id']   = $task->milestone_id;
                $project_task['created_by']     = \Auth::user()->creatorId();
                $project_task['stage']          = $task->stage;
                $project_task['status']         = $task->status;
                $project_task['order']          = $task->order;
                $project_task->save();

                if (in_array("task_checklist", $request->task)) {
                    $task_checklists = ProjectTaskCheckList::where('task_id', $task->id)->get();
                    foreach ($task_checklists as $task_checklist) {
                        $taskchecklist                = new ProjectTaskCheckList();
                        $taskchecklist['name']        = $task_checklist->name;
                        $taskchecklist['task_id']     = $project_task->id;
                        $taskchecklist['created_by']  = $task_checklist->created_by;
                        $taskchecklist['status']      = $task_checklist->status;
                        $taskchecklist->save();
                    }
                }
                if (in_array("task_comment", $request->task)) {
                    $task_comments = ProjectTaskComment::where('task_id', $task->id)->get();
                    foreach ($task_comments as $task_comment) {
                        $comment                = new ProjectTaskComment();
                        $comment['comment']     = $task_comment->comment;
                        $comment['created_by']  = $task_comment->created_by;
                        $comment['task_id']     = $project_task->id;
                        $comment['user_type']   = $task_comment->user_type;
                        $comment->save();
                    }
                }
                if (in_array("task_files", $request->task)) {
                    $task_files = ProjectTaskFile::where('task_id', $task->id)->get();
                    foreach ($task_files as $task_file) {
                        $file               = new ProjectTaskFile();
                        $file['file']       = $task_file->file;
                        $file['name']       = $task_file->name;
                        $file['extension']  = $task_file->extension;
                        $file['file_size']  = $task_file->file_size;
                        $file['created_by'] = $task_file->created_by;
                        $file['task_id']    = $project_task->id;
                        $file['user_type']  = $task_file->user_type;
                        $file->save();
                    }
                }
            }
        }

        if (isset($request->milestone) && in_array("milestone", $request->milestone)) {
            $milestones = ProjectMilestone::where('project_id', $project->id)->get();
            foreach ($milestones as $milestone) {
                $post                   = new ProjectMilestone();
                $post['project_id']     = $duplicate->id;
                $post['title']          = $milestone->title;
                $post['status']         = $milestone->status;
                $post['cost']           = $milestone->cost;
                $post['description']       = $milestone->description;
                $post['start_date']     = $milestone->start_date;
                $post['due_date']       = $milestone->due_date;
                $post->save();
            }
        }
        if (isset($request->project_file) && in_array("project_file", $request->project_file)) {
            $project_files = ProjectFile::where('project_id', $project->id)->get();
            foreach ($project_files as $project_file) {
                $ProjectFile                = new ProjectFile();
                $ProjectFile['project_id']  = $duplicate->id;
                $ProjectFile['file']   = $project_file->file;
                $ProjectFile['description']   = $project_file->description;
                $ProjectFile->save();
            }
        }

        //webhook
        $module = "New Project";
        $webhook = Utility::webhookSetting($module);
        if ($webhook) {
            $parameter = json_encode($project);

            // 1 parameter is URL , 2  (Project Data) parameter is data , 3 parameter is method
            $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
            if ($status == true) {
                return redirect()->back()->with('success', __('Project successfully created.'));
            } else {
                return redirect()->back()->with('error', __('Project call failed.'));
            }
        }

        return redirect()->back()->with('success', 'Project Created Successfully');
    }

    public function calendar(Request $request)
    {

        if (\Auth::user()->type == 'employee') {
            $userTask = ProjectTask::where('assign_to', \Auth::user()->id)->whereMonth('start_date', date('m'))->get();
        } else {
            $userTask = ProjectTask::where('created_by', \Auth::user()->id)->whereMonth('start_date', date('m'))->get();
        }
        return view('project.taskcalendar', compact('userTask'));
    }

    public function  get_holiday_data(Request $request)
    {
        $arrayJson = [];

        if ($request->get('calender_type') == 'google_calender') {

            $type = 'task';
            $arrayJson = Utility::getCalendarData($type);
        } else {
            // $data = localMeeting::get();

            if (\Auth::user()->type == 'employee') {
                $userTask = ProjectTask::where('assign_to', \Auth::user()->id)->get();
            } else {
                $userTask = ProjectTask::where('created_by', \Auth::user()->id)->get();
            }
            foreach ($userTask as $val) {
                $end_date = date_create($val->end_date);
                date_add($end_date, date_interval_create_from_date_string("1 days"));
                $arrayJson[] = [
                    "id" => $val->id,
                    "title" => $val->title,
                    "start" => $val->start_date,
                    "end" => $val->due_date,
                    "className" => 'event-primary',
                    "textColor" => '#FFF',
                    "allDay" => true,
                    "url" => route('project.task.edit', $val->id),
                ];
            }
        }

        return $arrayJson;
    }
}

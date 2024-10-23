<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Contract;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\Employee;
use App\Models\Estimate;
use App\Models\event;
use App\Models\Goal;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\Meeting;
use App\Models\Order;
use App\Models\Pipeline;
use App\Models\Plan;
use App\Models\Project;
use App\Models\Projects;
use App\Models\ProjectStage;
use App\Models\ProjectTask;
use App\Models\Support;
use App\Models\User;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Cookie;

class DashboardController extends Controller
{
    public function index()
    {
        // $getInvoiceProductsData        = Utility::getInvoiceProductsData();
        
        if(\Auth::check())
        {

            $data['estimateOverviewColor'] = Estimate::$statuesColor;
            $data['invoiceOverviewColor']  = Invoice::$statuesColor;
            $data['projectStatusColor']    = Project::$projectStatusColor;

            if(\Auth::user()->type == 'super admin')
            {
                $user                       = \Auth::user();
                $user['total_user']         = $user->countCompany();
                $user['total_paid_user']    = $user->countPaidCompany();
                $user['total_orders']       = Order::total_orders();
                $user['total_orders_price'] = Order::total_orders_price();
                $user['total_plan']         = Plan::total_plan();
                $user['most_purchese_plan'] = (!empty(Plan::most_purchese_plan()) ? Plan::most_purchese_plan()->name : '');
                $chartData                  = $this->getOrderChart(['duration' => 'week']);

                return view('dashboard.super_admin', compact('user', 'chartData'));
            }
            elseif(\Auth::user()->type == 'company')
            {
                $data['totalClient']     = User::where('created_by', \Auth::user()->creatorId())->where('type', 'client')->count();
                $data['totalEmployee']   = User::where('created_by', \Auth::user()->creatorId())->where('type', 'employee')->count();
                $data['totalProject']    = $totalProject = Project::where('created_by', \Auth::user()->creatorId())->count();
                $data['totalEstimation'] = $totalEstimation = Estimate::where('created_by', \Auth::user()->creatorId())->count();
                $data['totalInvoice']    = $totalInvoice = Invoice::where('created_by', \Auth::user()->creatorId())->count();
                $data['totalLead']       = Lead::where('created_by', \Auth::user()->creatorId())->count();
                $data['totalDeal']       = Deal::where('created_by', \Auth::user()->creatorId())->count();
                $data['totalItem']       = Item::where('created_by', \Auth::user()->creatorId())->count();

                $estimationStatus = Estimate::$statues;
                $estimations      = [];

                $statusColor = [
                    'success',
                    'info',
                    'warning',
                    'danger',
                ];

                foreach($estimationStatus as $k => $status)
                {
                    $estimation['status']     = $status;
                    $estimation['total']      = $total = Estimate::where('created_by', \Auth::user()->creatorId())->where('status', $k)->count();
                    $percentage               = ($totalEstimation != 0) ? ($total * 100) / $totalEstimation : '0';
                    $estimation['percentage'] = number_format($percentage, 2);
                    $estimations[]            = $estimation;
                }

                $invoiceStatus = Invoice::$statues;
                $invoices      = [];
                foreach($invoiceStatus as $k => $status)
                {
                    $invoice['status']     = $status;
                    $invoice['total']      = $total = Invoice::where('created_by', \Auth::user()->creatorId())->where('status', $k)->count();
                    $percentage            = ($totalInvoice != 0) ? ($total * 100) / $totalInvoice : '0';
                    $invoice['percentage'] = number_format($percentage, 2);
                    $invoices[]            = $invoice;
                }


                $projectStatus = Project::$projectStatus;
                $projects      = $projectLabel = $projectData = [];

                foreach($projectStatus as $k => $status)
                {
                    $project['status']     = $projectLabel[] = $status;
                    $project['total']      = $total = Project::where('created_by', \Auth::user()->creatorId())->where('status', $k)->count();
                    $percentage            = ($totalProject != 0) ? ($total * 100) / $totalProject : '0';
                    $project['percentage'] = $projectData[] = number_format($percentage, 2);
                    $projects[]            = $project;
                }

                $data['topDueInvoice']      = Invoice::where('created_by', \Auth::user()->creatorId())
                    ->where('due_date', '<', date('Y-m-d'))->with(['items', 'payments', 'creditNote','clients'])->limit(5)->get();
                $data['topDueProject']      = Project::where('created_by', \Auth::user()->creatorId())->where('due_date', '<', date('Y-m-d'))->limit(5)->get();
                $data['topDueTask']         = ProjectTask::select('project_tasks.*', 'projects.title as project_title')->leftjoin('projects', 'project_tasks.project_id', 'projects.id')->where('projects.created_by', \Auth::user()->creatorId())->where('project_tasks.due_date', '<', date('Y-m-d'))->limit(5)->with('taskUser')->get();
                $data['topMeeting']         = Meeting::where('created_by', \Auth::user()->creatorId())->where('date', '>', date('Y-m-d'))->limit(5)->get();
                $data['thisWeekEvent']      = Event::whereBetween(
                    'start_date', [
                                    Carbon::now()->startOfWeek(),
                                    Carbon::now()->endOfWeek(),
                                ]
                )->where('created_by', \Auth::user()->creatorId())->limit(5)->get();
                $data['contractExpirySoon'] = Contract::where('created_by', \Auth::user()->creatorId())->whereMonth('start_date', date('m'))->whereYear('start_date', date('Y'))->whereMonth('end_date', date('m'))->whereYear('end_date', date('Y'))->get();

                $date               = \Carbon\Carbon::today()->subDays(7);
                $data['newTickets'] = Support::where('created_by', \Auth::user()->creatorId())->where('created_at', '>', $date)->get();
                $data['newClients'] = User::where('created_by', \Auth::user()->creatorId())->where('type', 'client')->orderBy('id', 'desc')->limit(5)->get();

                $data['estimationOverview'] = $estimations;
                $data['invoiceOverview']    = $invoices;
                $data['projects']           = $projects;
                $data['projectLabel']       = $projectLabel;
                $data['projectData']        = $projectData;


                $data['goals'] = Goal::where('created_by', '=', \Auth::user()->creatorId())->where('display', 1)->get();

                $data['pipelines']     = Pipeline::where('created_by', '=', \Auth::user()->creatorId())->count();
                $data['leadStages']    = LeadStage::where('created_by', '=', \Auth::user()->creatorId())->count();
                $data['dealStages']    = DealStage::where('created_by', '=', \Auth::user()->creatorId())->count();
                $data['projectStages'] = ProjectStage::where('created_by', '=', \Auth::user()->creatorId())->count();

                $users = User::find(\Auth::user()->creatorId());
                $plan = Plan::find($users->plan);

                if($plan->storage_limit > 0)
                {
                $storage_limit = ($users->storage_limit / $plan->storage_limit) * 100;
                }
                else{
                    $storage_limit = 0;
                }
                return view('dashboard.index', compact('data','users','plan','storage_limit'));
            }
            elseif(\Auth::user()->type == 'client')
            {

                $data['totalProject']    = $totalProject = Project::where('client', \Auth::user()->id)->count();
                $data['totalEstimation'] = $totalEstimation = Estimate::where('client', \Auth::user()->id)->count();
                $data['totalInvoice']    = $totalInvoice = Invoice::where('client', \Auth::user()->id)->count();
                $data['totalDeal']       = Deal::leftjoin('client_deals', 'client_deals.deal_id', 'deals.id')->where('client_deals.client_id', \Auth::user()->id)->count();


                $estimationStatus = Estimate::$statues;
                $estimations      = [];
                foreach($estimationStatus as $k => $status)
                {
                    $estimation['status']     = $status;
                    $estimation['total']      = $total = Estimate::where('client', \Auth::user()->id)->where('status', $k)->count();
                    $percentage               = ($totalEstimation != 0) ? ($total * 100) / $totalEstimation : '0';
                    $estimation['percentage'] = number_format($percentage, 2);
                    $estimations[]            = $estimation;
                }


                $invoiceStatus = Invoice::$statues;
                $invoices      = [];
                foreach($invoiceStatus as $k => $status)
                {
                    $invoice['status']     = $status;
                    $invoice['total']      = $total = Invoice::where('client', \Auth::user()->id)->where('status', $k)->count();
                    $percentage            = ($totalInvoice != 0) ? ($total * 100) / $totalInvoice : '0';
                    $invoice['percentage'] = number_format($percentage, 2);
                    $invoices[]            = $invoice;
                }


                $projectStatus = Project::$projectStatus;
                $projects      = $projectLabel = $projectData = [];

                foreach($projectStatus as $k => $status)
                {
                    $project['status']     = $projectLabel[] = $status;
                    $project['total']      = $total = Project::where('client', \Auth::user()->id)->where('status', $k)->count();
                    $percentage            = ($totalProject != 0) ? ($total * 100) / $totalProject : '0';
                    $project['percentage'] = $projectData[] = number_format($percentage, 2);
                    $projects[]            = $project;
                }

                $data['topDueInvoice'] = Invoice::where('client', \Auth::user()->id)->where('due_date', '<', date('Y-m-d'))->limit(5)->with('clients')->get();
                $data['topDueProject'] = Project::where('client', \Auth::user()->id)->where('due_date', '<', date('Y-m-d'))->limit(5)->get();

                $data['contractExpirySoon'] = Contract::where('client', \Auth::user()->id)->whereMonth('start_date', date('m'))->whereYear('start_date', date('Y'))->whereMonth('end_date', date('m'))->whereYear('end_date', date('Y'))->get();

                $date = \Carbon\Carbon::today()->subDays(7);


                $data['estimationOverview'] = $estimations;
                $data['invoiceOverview']    = $invoices;
                $data['projects']           = $projects;
                $data['projectLabel']       = $projectLabel;
                $data['projectData']        = $projectData;

                $data['goals'] = Goal::where('created_by', '=', \Auth::user()->creatorId())->where('display', 1)->get();


                $users = User::find(\Auth::user()->creatorId());
                $plan = Plan::find($users->plan);

                if($plan->storage_limit > 0)
                {
                $storage_limit = ($users->storage_limit / $plan->storage_limit) * 100;
                }
                else{
                    $storage_limit = 0;
                }
                return view('dashboard.index', compact('data','users','plan','storage_limit'));

                // return view('dashboard.index', compact('data'));
            }
            elseif(\Auth::user()->type == 'employee')
            {

                $data['totalProject'] = $totalProject = Project::leftjoin('project_users', 'project_users.project_id', 'projects.id')->where('project_users.user_id', \Auth::user()->id)->count();
                $data['totalLead']    = Lead::where('user_id', \Auth::user()->id)->count();

                $data['totalDeal'] = Deal::leftjoin('user_deals', 'user_deals.deal_id', 'deals.id')->where('user_deals.user_id', \Auth::user()->id)->count();
                $data['totalItem'] = Item::where('created_by', \Auth::user()->creatorId())->count();


                $projectStatus = Project::$projectStatus;
                $projects      = $projectLabel = $projectData = [];

                foreach($projectStatus as $k => $status)
                {
                    $project['status']     = $projectLabel[] = $status;
                    $project['total']      = $total = Project::leftjoin('project_users', 'project_users.project_id', 'projects.id')->where('project_users.user_id', \Auth::user()->id)->where('status', $k)->count();
                    $percentage            = ($totalProject != 0) ? ($total * 100) / $totalProject : '0';
                    $project['percentage'] = $projectData[] = number_format($percentage, 2);
                    $projects[]            = $project;
                }


                $data['topDueProject'] = Project::leftjoin('project_users', 'project_users.project_id', 'projects.id')->where('project_users.user_id', \Auth::user()->id)->where('due_date', '<', date('Y-m-d'))->limit(5)->get();
                $data['topDueTask']    = ProjectTask::where('assign_to', \Auth::user()->id)->where('due_date', '<', date('Y-m-d'))->limit(5)->get();

                $employee           = Employee::where('user_id', \Auth::user()->id)->first();
                $data['topMeeting'] = Meeting::where('department', 0)->orWhereIn(
                    'designation', [
                                     0,
                                     $employee->designation,
                                 ]
                )->orWhereIn(
                    'department', [
                                    0,
                                    $employee->department,
                                ]
                )->where('date', '>', date('Y-m-d'))->limit(5)->get();

                $data['thisWeekEvent'] = Event::whereBetween(
                    'start_date', [
                                    Carbon::now()->startOfWeek(),
                                    Carbon::now()->endOfWeek(),
                                ]
                )->whereIn(
                    'department', [
                                    0,
                                    $employee->department,
                                ]
                )->orWhereIn(
                    'employee', [
                                  0,
                                  \Auth::user()->id,
                              ]
                )->limit(5)->get();


                $data['projects']     = $projects;
                $data['projectLabel'] = $projectLabel;
                $data['projectData']  = $projectData;

                $data['goals'] = Goal::where('created_by', '=', \Auth::user()->creatorId())->where('display', 1)->get();

                $date                       = date("Y-m-d");
                $data['employeeAttendance'] = Attendance::orderBy('id', 'desc')->where('employee_id', '=', \Auth::user()->id)->where('date', '=', $date)->first();

                $users = User::find(\Auth::user()->creatorId());
                $plan = Plan::find($users->plan);

                if($plan->storage_limit > 0)
                {
                $storage_limit = ($users->storage_limit / $plan->storage_limit) * 100;
                }
                else{
                    $storage_limit = 0;
                }
                return view('dashboard.index', compact('data','users','plan','storage_limit'));
                // return view('dashboard.index', compact('data'));
            }

        }
        else
        {

            if(!file_exists(storage_path() . "/installed"))
            {
                header('location:install');
                die;
            }
            else
            {
                $settings = Utility::settings();

                if ($settings['display_landing_page'] == 'on' && \Schema::hasTable('landing_page_settings')) {
                    return view('landingpage::layouts.landingpage');
                } else {
                    return redirect('login');
                }
            }


        }

    }

    public function getOrderChart($arrParam)
    {
        $arrDuration = [];
        if($arrParam['duration'])
        {
            if($arrParam['duration'] == 'week')
            {
                $previous_week = strtotime("-2 week +1 day");
                for($i = 0; $i < 14; $i++)
                {
                    $arrDuration[date('Y-m-d', $previous_week)] = date('d-M', $previous_week);
                    $previous_week                              = strtotime(date('Y-m-d', $previous_week) . " +1 day");
                }
            }
        }

        $arrTask          = [];
        $arrTask['label'] = [];
        $arrTask['data']  = [];
        foreach($arrDuration as $date => $label)
        {

            $data               = Order::select(\DB::raw('count(*) as total'))->whereDate('created_at', '=', $date)->first();
            $arrTask['label'][] = $label;
            $arrTask['data'][]  = $data->total;
        }

        return $arrTask;
    }

    public function changeLang($lang)
    {
        $changelanguage=Cookie::queue('LANGUAGE',$lang, 120);

        return redirect()->back()->with('success', __('Language Change Successfully!'));
    }

    public function search(Request $request)
    {
        $html   = '';
        $usr    = Auth::user();
        $type   = $usr->type;
        $search = $request->keyword;

        if(!empty($search))
        {
            if($type == 'Client')
            {
                $objDeal = Deal::select(
                    [
                        'deals.id',
                        'deals.name',
                    ]
                )->join('client_deals', 'client_deals.deal_id', '=', 'deals.id')->whereRaw('FIND_IN_SET(' . $usr->id . ',client_deals.client_id)')->where('deals.name', 'LIKE', $search . "%")->get();

                $html .= '<li>
                            <span class="list-link">
                                <i class="ti ti-search"></i>' . __('Deals') . '
                            </span>
                        </li>';

                if($objDeal->count() > 0)
                {
                    foreach($objDeal as $deal)
                    {
                        $html .= '<li>
                            <a class="list-link pl-4" href="' . route('deals.show', $deal->id) . '">
                                <span>' . $deal->name . '</span>
                            </a>
                        </li>';
                    }
                }
                else
                {
                    $html .= '<li>
                                <a class="list-link pl-4" href="#">
                                    <span>' . __('No Deals Found.') . '</span>
                                </a>
                            </li>';
                }
            }
            else
            {
                // Deal Wise Searching
                $objDeal = Deal::select(
                    [
                        'deals.id',
                        'deals.name',
                    ]
                )->join('user_deals', 'user_deals.deal_id', '=', 'deals.id')->where('user_deals.user_id', '=', $usr->id)->where('deals.name', 'LIKE', $search . "%")->get();

                $html .= '<li>
                            <span class="list-link">
                                <i class="ti ti-search"></i>' . __('Deals') . '
                            </span>
                        </li>';

                if($objDeal->count() > 0)
                {
                    foreach($objDeal as $deal)
                    {
                        $html .= '<li>
                            <a class="list-link pl-4" href="' . route('deals.show', $deal->id) . '">
                                <span>' . $deal->name . '</span>
                            </a>
                        </li>';
                    }
                }
                else
                {
                    $html .= '<li>
                                <a class="list-link pl-4" href="#">
                                    <span>' . __('No Deals Found.') . '</span>
                                </a>
                            </li>';
                }
                // Deal Wise Searching end

                // Task Wise Searching
                $objTask = Deal::select(
                    [
                        'deal_tasks.id',
                        'deal_tasks.name',
                        'deals.id AS deal_id',
                    ]
                )->join('user_deals', 'user_deals.deal_id', '=', 'deals.id')->join('deal_tasks', 'deal_tasks.deal_id', '=', 'deals.id')->where('user_deals.user_id', '=', $usr->id)->where('deal_tasks.name', 'LIKE', $search . "%")->get();

                $html .= '<li>
                            <span class="list-link">
                                <i class="ti ti-search"></i>' . __('Tasks') . '
                            </span>
                        </li>';

                if($objTask->count() > 0)
                {
                    foreach($objTask as $task)
                    {
                        $html .= '<li>
                            <a class="list-link pl-4" href="' . route('deals.show', $task->deal_id) . '">
                                <span>' . $task->name . '</span>
                            </a>
                        </li>';
                    }
                }
                else
                {
                    $html .= '<li>
                                <a class="list-link pl-4" href="#">
                                    <span>' . __('No Tasks Found.') . '</span>
                                </a>
                            </li>';
                }

                // Task Wise Searching End

                // Lead Wise Searching
                $objLead = Lead::select(
                    [
                        'leads.id',
                        'leads.name',
                    ]
                )->join('user_leads', 'user_leads.lead_id', '=', 'leads.id')->where('user_leads.user_id', '=', $usr->id)->where('leads.name', 'LIKE', $search . "%")->get();

                $html .= '<li>
                            <span class="list-link">
                                <i class="ti ti-search"></i>' . __('Leads') . '
                            </span>
                        </li>';

                if($objLead->count() > 0)
                {
                    foreach($objLead as $lead)
                    {

                        $html .= '<li>
                            <a class="list-link pl-4" href="' . route('leads.show', $lead->id) . '">
                                <span>' . $lead->name . '</span>
                            </a>
                        </li>';
                    }
                }
                else
                {
                    $html .= '<li>
                                <a class="list-link pl-4" href="#">
                                    <span>' . __('No Leads Found.') . '</span>
                                </a>
                            </li>';
                }
                // Lead Wise Searching End
            }
        }
        else
        {
            $html .= '<li>
                        <a class="list-link pl-4" href="#">
                        <i class="ti ti-search"></i>
                            <span>' . __('Type and search By Deal, Lead and Tasks.') . '</span>
                        </a>
                      </li>';
        }

        print_r($html);
    }



}


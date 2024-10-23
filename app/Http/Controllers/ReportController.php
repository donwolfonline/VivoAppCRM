<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Estimate;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Deal;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Project;
use App\Models\ProjectStage;
use App\Models\ProjectTask;
use App\Models\Timesheet;
use App\Models\Utility;
use App\Models\User;
use App\Models\StockReport;
use App\Models\ChartOfAccount;
use App\Models\JournalItem;
use App\Models\ChartOfAccountType;
use Illuminate\Http\Request;
use App\Models\ChartOfAccountSubType;
use App\Exports\task_reportExport;
use App\Exports\InvoiceExport;
use App\Exports\LeadExport;
use App\Exports\DealExport;
use App\Exports\LeaveReportExport;
use App\Exports\TimelogExport;
use App\Exports\FinanceExport;
use App\Exports\ClientExport;
use App\Exports\EstimateExport;
use App\Exports\StockReportExport;
use Maatwebsite\Excel\Facades\Excel;


class ReportController extends Controller
{
    public function task(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            
            $projects    = Project::where('created_by', \Auth::user()->creatorId());
            $projectList = clone $projects;
            if (!empty($request->project)) {
                $projects->where('id', $request->project);      
            }
            $projects = $projects->get();

            $projectStages = ProjectStage::where('created_by', \Auth::user()->creatorId())->get();       
            $stages        = $label = $color = $data = [];
            $total         = 0;

            $filter['endDateRange']   = $end_date = date('Y-m-d');
            $filter['startDateRange'] = $start_date = date('Y-m-d', strtotime('-30 days'));
            $filter['project']        = __('All');
            $filter['employee']       = __('All');
                      
            if (!empty($projectStages)) {
                $allTask = ProjectTask::whereIn('stage', $projectStages->pluck('id'));
                if (isset($request->project) && !empty($request->project)) {
                    $allTask->where('project_id', $request->project);                   
                }

                if ((isset($request->start_date) && !empty($request->start_date)) && (isset($request->end_date) && !empty($request->end_date))) {      
                    $allTask->whereBetween(
                        'start_date',
                        [
                            $request->start_date,
                            $request->end_date,
                        ]
                    );
                } else {                
                    $allTask->whereBetween(              
                        'start_date',
                        [               
                            $start_date,              
                            $end_date,  
                        ]
                    );
                }


                if (!empty($request->employee)) {
                    $allTask->where('assign_to', $request->employee);
                }
                $allTask = $allTask->count();
            }

            if(isset($request->project) && !empty($request->project))
            {
                $proj              = Project::find($request->project);
            }
            if(isset($request->employee) && !empty($request->employee))
            {
                $emp                = User::find($request->employee);
            }



            foreach ($projectStages as $stage) {


                $tasks = ProjectTask::where('stage', $stage->id);

                if (isset($request->project) && !empty($request->project)) {
                    $tasks->where('project_id', $request->project);
                    //dd($tasks);
                    // $proj              = Project::find($request->project);
                    $filter['project'] = $proj->title;
                }

                if ((isset($request->start_date) && !empty($request->start_date)) && (isset($request->end_date) && !empty($request->end_date))) {
                    $tasks->whereBetween(
                        'start_date',
                        [
                            $request->start_date,
                            $request->end_date,
                        ]
                    );
                    $filter['startDateRange'] = $request->start_date;
                    $filter['endDateRange']   = $request->end_date;
                } else {


                    $tasks->whereBetween(
                        'start_date',
                        [
                            $start_date,
                            $end_date,
                        ]
                    );
                }


                if (!empty($request->employee)) {
                    $tasks->where('assign_to', $request->employee);
                    // $emp                = User::find($request->employee);
                    $filter['employee'] = $emp->name;
                }

                $task['stage']      = $label[] = $stage->name;
                $task['color']      = $color[] = $stage->color;
                $task['total']      = $totalTask = $data[] = $tasks->count();
                $task['percentage'] = ($allTask != 0) ? number_format(($totalTask / $allTask) * 100, 2) : 0;

                $stages[] = $task;
                $total    += $totalTask;
            }

            $projectList = $projectList->pluck('title', 'id');
            $projectList->prepend('Select Project', '');
            
            $employees = User::where('created_by', \Auth::user()->creatorId())->where('type', 'employee')->get()->pluck('name', 'id');
            $employees->prepend('Select Employee', '');


            return view('report.task', compact('projects', 'stages', 'total', 'label', 'data', 'color', 'projectList', 'employees', 'filter'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function timelog(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $filter['endDateRange']   = $end_date = date('Y-m-d');
            $filter['startDateRange'] = $start_date = date('Y-m-d', strtotime('-15 days'));
            $filter['project']        = __('All');
            $filter['employee']       = __('All');
            $filter['task']           = __('All');

            $timesheets = Timesheet::where('created_by', \Auth::user()->creatorId());


            if ((isset($request->start_date) && !empty($request->start_date)) && (isset($request->end_date) && !empty($request->end_date))) {
                $timesheets->whereBetween(
                    'start_date',
                    [
                        $request->start_date,
                        $request->end_date,
                    ]
                );


                $filter['startDateRange'] = $request->start_date;
                $filter['endDateRange']   = $request->end_date;
            } else {


                $timesheets->whereBetween(
                    'start_date',
                    [
                        $start_date,
                        $end_date,
                    ]
                );
            }


            if (isset($request->project) && !empty($request->project)) {
                $timesheets->where('project_id', $request->project);


                $proj              = Project::find($request->project);
                $filter['project'] = $proj->title;
            }

            if (isset($request->task) && !empty($request->task)) {
                $timesheets->where('task_id', $request->task);

                $task           = ProjectTask::find($request->task);
                $filter['task'] = $task->title;
            }

            if (isset($request->employee) && !empty($request->employee)) {
                $timesheets->where('employee', $request->employee);

                $emp                = User::find($request->employee);
                $filter['employee'] = $emp->name;
            }

            $timesheets = $timesheets->get();


            $projectList = Project::where('created_by', \Auth::user()->creatorId())->get()->pluck('title', 'id');
            $projectList->prepend('Select Project', '');

            $employees = User::where('created_by', \Auth::user()->creatorId())->where('type', 'employee')->get()->pluck('name', 'id');
            $employees->prepend('Select User', '');

            $tasks = ProjectTask::select('project_tasks.*')->leftjoin('projects', 'project_tasks.project_id', 'projects.id')->get()->pluck('title', 'id');
            $tasks->prepend('Select Task', '');

            $labels = [];
            $data   = [];

            $start = strtotime($filter['startDateRange']);
            $end   = strtotime($filter['endDateRange']);

            $currentdate = $start;

            while ($currentdate <= $end) {

                $currentDateFormat = date('Y-m-d', $currentdate);

                $timesheetsFilter = Timesheet::where('created_by', \Auth::user()->creatorId())->where('start_date', $currentDateFormat);

                if (isset($request->project) && !empty($request->project)) {
                    $timesheetsFilter->where('project_id', $request->project);
                }

                if (isset($request->task) && !empty($request->task)) {
                    $timesheetsFilter->where('task_id', $request->task);
                }
                if (isset($request->employee) && !empty($request->employee)) {
                    $timesheetsFilter->where('employee', $request->employee);
                }
                $timesheetsFilter = $timesheetsFilter->get();

                $hours = 0;
                foreach ($timesheetsFilter as $timesheet) {
                    $t1    = strtotime($timesheet->end_date . ' ' . $timesheet->end_time);
                    $t2    = strtotime($timesheet->start_date . ' ' . $timesheet->start_time);
                    $diff  = $t1 - $t2;
                    // dd($diff);
                    $hours = number_format($diff / (60 * 60), 2);
                }
 
                $currentdate = strtotime('+1 days', $currentdate);
                $labels[]    = date('d-M', strtotime($currentDateFormat));
                // dd($labels);
                $data[]      = $hours;
                // dd($data);
            }


            return view('report.timelog', compact('timesheets', 'projectList', 'employees', 'tasks', 'labels', 'data', 'filter'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function finance(Request $request)
    {
        $getInvoiceProductsData = Utility::getInvoiceProductsData();
        
        if (\Auth::user()->type == 'company') {
            $filter['project'] = __('All');
            $filter['client']  = __('All');

            $projectList = Project::where('created_by', \Auth::user()->creatorId())->get()->pluck('title', 'id');
            $projectList->prepend('Select Project', '');

            $clients = User::where('created_by', \Auth::user()->creatorId())->where('type', 'client')->get()->pluck('name', 'id');
            $clients->prepend('Select User', '');

            $invoices = Invoice::where('created_by', \Auth::user()->creatorId());

            if (!empty($request->start_month) && !empty($request->end_month)) {
                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);
            } else {
                $start = strtotime(date('Y-01'));
                $end   = strtotime(date('Y-12'));
            }

            $invoices->where('issue_date', '>=', date('Y-m-01', $start))->where('issue_date', '<=', date('Y-m-t', $end));

            if (isset($request->project) && !empty($request->project)) {
                $invoices->where('project', $request->project);
                $proj              = Project::find($request->project);
                $filter['project'] = $proj->title;
            }

            if (isset($request->client) && !empty($request->client)) {
                $invoices->where('client', $request->client);
                $client           = User::find($request->client);
                $filter['client'] = $client->name;
            }

            $invoices = $invoices->get();

            $invoices->map(function ($invoice) use ($getInvoiceProductsData){
                $invoice->totalAmt = $getInvoiceProductsData[$invoice->id]->total;
                return $invoice;
            });

            $labels = [];
            $data   = [];

            $currentdate = $start;

            while ($currentdate <= $end) {
                $monthYearList[] = date('Y-m', $currentdate);

                $currentdate = strtotime('+1 month', $currentdate);
            }

            $invoicesTotal = $invoicesDue = $invoicesTax = $invoicesDiscount = 0;
            foreach ($monthYearList as $monthYearDate) {
                $dateFormat = strtotime($monthYearDate);
                $month      = date('m', $dateFormat);
                $year       = date('Y', $dateFormat);

                $invoicesFilter = Invoice::where('created_by', \Auth::user()->creatorId())->whereMonth('issue_date', $month)->whereYear('issue_date', $year);

                if (isset($request->project) && !empty($request->project)) {
                    $invoicesFilter->where('project', $request->project);
                }
                if (isset($request->client) && !empty($request->client)) {
                    $invoicesFilter->where('client', $request->client);
                }
                $invoicesFilter = $invoicesFilter->get();


                $total = $due = $tax = $discount = 0;
                foreach ($invoicesFilter as $invoice) {
                    $total    += $invoice->getTotal();
                    $due      += $invoice->getDue();
                    $tax      += $invoice->getTotalTax();
                    $discount += $invoice->getTotalDiscount();
                }
                $invoicesTotal    += $total;
                $invoicesDue      += $due;
                $invoicesTax      += $tax;
                $invoicesDiscount += $discount;
                $data[]           = $total;
                $labels[]         = date('M Y', $dateFormat);
            }

            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange']   = date('M-Y', $end);

            return view('report.finance', compact('invoices', 'projectList', 'clients', 'labels', 'data', 'filter', 'invoicesTotal', 'invoicesDue', 'invoicesTax', 'invoicesDiscount'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function incomeVsExpense(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $invoices     = Invoice::where('created_by', \Auth::user()->creatorId());
            $labels       = $data = [];
            $expenseCount = $incomeCount = 0;

            if (!empty($request->start_month) && !empty($request->end_month)) {
                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);
            } else {
                $start = strtotime(date('Y-01'));
                $end   = strtotime(date('Y-12'));
            }


            $invoicesFilter = Invoice::selectRaw('invoices.*,MONTH(send_date) as month,YEAR(send_date) as year')->where('created_by', \Auth::user()->creatorId())->where('send_date', '>=', date('Y-m-01', $start))->where('send_date', '<=', date('Y-m-t', $end))->get();

            $invoicesTotal     = 0;
            $invoiceTotalArray = [];
            foreach ($invoicesFilter as $invoice) {
                $invoicesTotal                        += $invoice->getTotal();
                $invoiceTotalArray[$invoice->month][] = $invoice->getTotal();
            }
            $incomeCount += $invoicesTotal;

            for ($i = 1; $i <= 12; $i++) {
                $incomeData[] = array_key_exists($i, $invoiceTotalArray) ? array_sum($invoiceTotalArray[$i]) : 0;
            }


            $expenseFilter    = Expense::selectRaw('expenses.*,MONTH(date) as month,YEAR(date) as year')->where('created_by', \Auth::user()->creatorId())->where('date', '>=', date('Y-m-01', $start))->where('date', '<=', date('Y-m-t', $end))->get();
            $expenseTotal     = 0;
            $expeseTotalArray = [];
            foreach ($expenseFilter as $expense) {
                $expenseTotal                        += $expense->amount;
                $expeseTotalArray[$expense->month][] = $expense->amount;
            }
            $expenseCount += $expenseTotal;

            for ($i = 1; $i <= 12; $i++) {
                $expenseData[] = array_key_exists($i, $expeseTotalArray) ? array_sum($expeseTotalArray[$i]) : 0;
            }

            $currentdate = $start;
            while ($currentdate <= $end) {
                $labels[]    = date('M Y', $currentdate);
                $currentdate = strtotime('+1 month', $currentdate);
            }


            $incomeArr['label']           = __('Income');
            $incomeArr['borderColor']     = '#6777ef';
            $incomeArr['fill']            = '!0';
            $incomeArr['backgroundColor'] = '#6777ef';
            $incomeArr[]                  = $incomeData;

            $expenseArr['label']           = __('Expense');
            $expenseArr['borderColor']     = '#fc544b';
            $expenseArr['fill']            = '!0';
            $expenseArr['backgroundColor'] = '#fc544b';
            $expenseArr['data']            = $expenseData;
            //            dd($labels);
            $data[] = $incomeArr;
            $data[] = $expenseArr;

            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange']   = date('M-Y', $end);

            return view('report.income_expense', compact('invoices', 'labels', 'data', 'incomeCount', 'expenseCount', 'filter', 'incomeData', 'expenseData'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function leave(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('Select Department', '');

            $filterYear['department']    = __('All');
            $filterYear['type']          = __('Monthly');
            $filterYear['dateYearRange'] = date('M-Y');
            $employees                   = Employee::where('created_by', \Auth::user()->creatorId());
            if (!empty($request->department)) {
                $employees->where('department', $request->department);
                $filterYear['department'] = !empty(Department::find($request->department)) ? Department::find($request->department)->name : '';
            }
            $employees = $employees->get();

            $leaves        = [];
            $totalApproved = $totalReject = $totalPending = 0;
            foreach ($employees as $employee) {

                $employeeLeave['id']          = $employee->user_id;
                $employeeLeave['employee_id'] = $employee->employee_id;
                $employeeLeave['employee']    = !empty($employee->users) ? $employee->users->name : '';

                $approved = Leave::where('employee_id', $employee->user_id)->where('status', 'Approve');
                $reject   = Leave::where('employee_id', $employee->user_id)->where('status', 'Reject');
                $pending  = Leave::where('employee_id', $employee->user_id)->where('status', 'Pending');

                if ($request->type == 'monthly' && !empty($request->month)) {
                    $month = date('m', strtotime($request->month));
                    $year  = date('Y', strtotime($request->month));

                    $approved->whereMonth('applied_on', $month)->whereYear('applied_on', $year);
                    $reject->whereMonth('applied_on', $month)->whereYear('applied_on', $year);
                    $pending->whereMonth('applied_on', $month)->whereYear('applied_on', $year);

                    $filterYear['dateYearRange'] = date('M-Y', strtotime($request->month));
                    $filterYear['type']          = __('Monthly');
                } elseif (!isset($request->type)) {
                    $month     = date('m');
                    $year      = date('Y');
                    $monthYear = date('Y-m');

                    $approved->whereMonth('applied_on', $month)->whereYear('applied_on', $year);
                    $reject->whereMonth('applied_on', $month)->whereYear('applied_on', $year);
                    $pending->whereMonth('applied_on', $month)->whereYear('applied_on', $year);

                    $filterYear['dateYearRange'] = date('M-Y', strtotime($monthYear));
                    $filterYear['type']          = __('Monthly');
                }


                if ($request->type == 'yearly' && !empty($request->year)) {
                    $approved->whereYear('applied_on', $request->year);
                    $reject->whereYear('applied_on', $request->year);
                    $pending->whereYear('applied_on', $request->year);


                    $filterYear['dateYearRange'] = $request->year;
                    $filterYear['type']          = __('Yearly');
                }

                $approved = $approved->count();
                $reject   = $reject->count();
                $pending  = $pending->count();

                $totalApproved += $approved;
                $totalReject   += $reject;
                $totalPending  += $pending;

                $employeeLeave['approved'] = $approved;
                $employeeLeave['reject']   = $reject;
                $employeeLeave['pending']  = $pending;


                $leaves[] = $employeeLeave;
            }

            $starting_year = date('Y', strtotime('-5 year'));
            $ending_year   = date('Y', strtotime('+5 year'));

            $filterYear['starting_year'] = $starting_year;
            $filterYear['ending_year']   = $ending_year;

            $filter['totalApproved'] = $totalApproved;
            $filter['totalReject']   = $totalReject;
            $filter['totalPending']  = $totalPending;


            return view('report.leave', compact('department', 'leaves', 'filterYear', 'filter'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function employeeLeave(Request $request, $employee_id, $status)
    {

        if (\Auth::user()->type == 'company') {
            $leaveTypes = LeaveType::where('created_by', \Auth::user()->creatorId())->get();
            $leaves     = [];
            foreach ($leaveTypes as $leaveType) {
                $leave        = new Leave();
                $leave->title = $leaveType->title;
                $leave->total = Leave::where('employee_id', $employee_id)->where('status', $status)->where('leave_type', $leaveType->id)->count();
                $leaves[]     = $leave;
            }
            $leaveData = Leave::where('employee_id', $employee_id)->where('status', $status)->get();

            return view('report.leaveShow', compact('leaves', 'leaveData'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function estimate(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $filter['status'] = __('All');
            $filter['client'] = __('All');

            $status = Estimate::$statues;

            $clients = User::where('created_by', \Auth::user()->creatorId())->where('type', 'client')->get()->pluck('name', 'id');
            $clients->prepend('Select User', '');

            $estimates = Estimate::orderBy('id');

            if (!empty($request->client)) {
                $estimates->where('client', $request->client);
                $client           = User::find($request->client);
                $filter['client'] = $client->name;
            }

            if ($request->status != '') {
                $estimates->where('status', $request->status);
                $filter['status'] = Estimate::$statues[$request->status];
            }

            if (!empty($request->start_month) && !empty($request->end_month)) {
                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);
            } else {
                $start = strtotime(date('Y-01'));
                $end   = strtotime(date('Y-12'));
            }

            $estimates->where('send_date', '>=', date('Y-m-01', $start))->where('send_date', '<=', date('Y-m-t', $end));

            $estimates->where('created_by', \Auth::user()->creatorId());
            $estimates = $estimates->get();

            $totalEstimation = $totalTax = $totalDiscount = 0;
            foreach ($estimates as $estimation) {
                $totalEstimation += $estimation->getTotal();
                $totalTax        += $estimation->getTotalTax();
                $totalDiscount   += $estimation->getTotalDiscount();
            }
            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange']   = date('M-Y', $end);

            return view('report.estimate', compact('status', 'clients', 'estimates', 'filter', 'totalEstimation', 'totalTax', 'totalDiscount'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function invoice(Request $request)
    {

        if (\Auth::user()->type == 'company') {
            $filter['status'] = __('All');
            $filter['client'] = __('All');

            $status = Invoice::$statues;

            $clients = User::where('created_by', \Auth::user()->creatorId())->where('type', 'client')->get()->pluck('name', 'id');
            $clients->prepend('Select Client', '');

            $invoices = Invoice::orderBy('id');

            if (!empty($request->client)) {
                $invoices->where('client', $request->client);
                $client           = User::find($request->client);
                $filter['client'] = $client->name;
            }

            if ($request->status != '') {
                $invoices->where('status', $request->status);
                $filter['status'] = Invoice::$statues[$request->status];
            }

            if (!empty($request->start_month) && !empty($request->end_month)) {
                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);
            } else {
                $start = strtotime(date('Y-01'));
                $end   = strtotime(date('Y-12'));
            }
            // dd(date('Y-12'));

            $invoices->where('send_date', '>=', date('Y-m-01', $start))->where('send_date', '<=', date('Y-m-t', $end));


            $invoices->where('created_by', \Auth::user()->creatorId());
            $invoices = $invoices->get();

            //dd($invoices);

            $totalInvoice = $totalDue = $totalTax = $totalDiscount = 0;
            foreach ($invoices as $invoice) {
                $totalInvoice  += $invoice->getTotal();
                $totalDue      += $invoice->getDue();
                $totalTax      += $invoice->getTotalTax();
                $totalDiscount += $invoice->getTotalDiscount();
            }
            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange']   = date('M-Y', $end);


            return view('report.invoice', compact('status', 'clients', 'invoices', 'filter', 'totalInvoice', 'totalDue', 'totalTax', 'totalDiscount'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function client(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $start = strtotime(date('Y-01'));
            $end   = strtotime(date('Y-12'));

            $filter['client'] = __('All');

            $clientFilter   = User::where('created_by', \Auth::user()->creatorId())->where('type', 'client');
            $clients        = clone $clientFilter;

            if (!empty($request->client)) {
                $clientFilter->where('id', $request->client);
                $client           = User::find($request->client);
                $filter['client'] = $client->name;
            }
            $clientFilter = $clientFilter->get();
            
            
            $clients = $clients->pluck('name', 'id');
            $clients->prepend('Select Client', '');

            $clientReport       = [];
            $clientTotalInvoice = $clientTotalAmount = $clientTotalDue = $clientTotalTax = $clientTotalDiscount = $clientTotalPaid = 0;
            foreach ($clientFilter as $client) {

                $clientData['client'] = $client->name;
                $totalAmount          = $totalTax = $totalDiscount = $totalDue = $totalPaid = 0;
                $clientInvoice        = Invoice::orderBy('id');

                if (!empty($request->start_month) && !empty($request->end_month)) {
                    $start = strtotime($request->start_month);
                    $end   = strtotime($request->end_month);
                }


                $clientInvoice->where('client', $client->id);
                $clientInvoice->where('send_date', '>=', date('Y-m-01', $start))->where('send_date', '<=', date('Y-m-t', $end));

                $clientInvoice->where('created_by', \Auth::user()->creatorId());
                $clientInvoice = $clientInvoice->get();

                $clientData['totalInvoice'] = count($clientInvoice);
                $clientTotalInvoice         += count($clientInvoice);

                foreach ($clientInvoice as $invoice) {
                    $totalAmount   += $invoice->getTotal();
                    $totalTax      += $invoice->getTotalTax();
                    $totalDiscount += $invoice->getTotalDiscount();
                    $totalDue      += $invoice->getDue();
                    $totalPaid     += $invoice->getTotal() - $invoice->getDue();
                }

                $clientTotalAmount   += $totalAmount;
                $clientTotalTax      += $totalTax;
                $clientTotalDiscount += $totalDiscount;
                $clientTotalDue      += $totalDue;
                $clientTotalPaid     += $totalPaid;


                $clientData['totalAmount']   = $totalAmount;
                $clientData['totalTax']      = $totalTax;
                $clientData['totalDiscount'] = $totalDiscount;
                $clientData['totalDue']      = $totalDue;
                $clientData['totalPaid']     = $totalPaid;
                $clientReport[]              = $clientData;
            }

            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange']   = date('M-Y', $end);

            return view('report.client', compact('clients', 'clientReport', 'filter', 'clientTotalInvoice', 'clientTotalAmount', 'clientTotalTax', 'clientTotalDiscount', 'clientTotalDue', 'clientTotalPaid'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function lead(Request $request)
    {
        // dd($request->users);
        if (\Auth::user()->type == 'company') {
            $labels = [];
            $data   = [];
            if (!empty($request->start_month) && !empty($request->end_month)) {
                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);
            } else {
                $start = strtotime(date('Y-01'));
                $end   = strtotime(date('Y-12'));
            }

            $leads = Lead::orderBy('id', 'desc');
            $leads->where('date', '>=', date('Y-m-01', $start))->where('date', '<=', date('Y-m-t', $end));
            $leads->where('created_by', \Auth::user()->creatorId());
            $leads = $leads->get();

            $users = User::where('type', 'employee')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $users->prepend('Select User', '');
            if (!empty($request->users)) {
                $leads = Lead::select('leads.*')->join('user_leads', 'user_leads.lead_id', '=', 'leads.id')->where('user_leads.user_id', '=', $request->users)->orderBy('leads.order', 'desc')->get();
            }

            $currentdate = $start;

            while ($currentdate <= $end) {
                $month = date('m', $currentdate);
                $year  = date('Y', $currentdate);

                $leadFilter = Lead::where('created_by', \Auth::user()->creatorId())->whereMonth('date', $month)->whereYear('date', $year)->get();

                $data[]      = count($leadFilter);
                $labels[]    = date('M Y', $currentdate);
                $currentdate = strtotime('+1 month', $currentdate);
            }

            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange']   = date('M-Y', $end);
            $filter['user_id']   = \Auth::user()->creatorId();

            return view('report.lead', compact('labels', 'data', 'filter', 'leads', 'users'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function deal(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $labels = [];
            $data   = [];

            if (!empty($request->start_month) && !empty($request->end_month)) {
                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);
            } else {
                $start = strtotime(date('Y-01'));
                $end   = strtotime(date('Y-12'));
            }

            $deals = Deal::orderBy('id');
            $deals->where('created_at', '>=', date('Y-m-01', $start))->where('created_at', '<=', date('Y-m-t', $end));
            $deals->where('created_by', \Auth::user()->creatorId());
            $deals = $deals->with('stage')->get();

            $users = User::where('type', 'employee')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $users->prepend('Select User', '');
            if (!empty($request->users)) {
                $deals = Deal::select('deals.*')->join('user_deals', 'user_deals.deal_id', '=', 'deals.id')->where('user_deals.user_id', '=', $request->users)->orderBy('deals.order')->with('stage')->get();
            }

            $currentdate = $start;
            while ($currentdate <= $end) {
                $month = date('m', $currentdate);
                $year  = date('Y', $currentdate);

                $dealFilter = Deal::where('created_by', \Auth::user()->creatorId())->whereMonth('created_at', $month)->whereYear('created_at', $year)->get();

                $data[]      = count($dealFilter);
                $labels[]    = date('M Y', $currentdate);
                $currentdate = strtotime('+1 month', $currentdate);
            }

            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange']   = date('M-Y', $end);

            return view('report.deal', compact('labels', 'data', 'filter', 'deals', 'users'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function attendance(Request $request)
    {
        if (\Auth::user()->type == 'company') {

            $department = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $department->prepend('Select Department', '');

            $data['department'] = __('All');

            $employees = Employee::where('created_by', \Auth::user()->creatorId());

            if (!empty($request->department)) {
                $employees->where('department', $request->department);
                $data['department'] = !empty(Department::find($request->department)) ? Department::find($request->department)->name : '';
            }

            $employees = $employees->get();


            if (!empty($request->month)) {
                $currentdate = strtotime($request->month);
                $month       = date('m', $currentdate);
                $year        = date('Y', $currentdate);
                $curMonth    = date('M-Y', strtotime($request->month));
            } else {
                $month    = date('m');
                $year     = date('Y');
                $curMonth = date('M-Y', strtotime($year . '-' . $month));
            }


            $num_of_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            for ($i = 1; $i <= $num_of_days; $i++) {
                $dates[] = str_pad($i, 2, '0', STR_PAD_LEFT);
            }

            $employeesAttendance = [];
            $totalPresent        = $totalLeave = $totalEarlyLeave = 0;
            $ovetimeHours        = $overtimeMins = $earlyleaveHours = $earlyleaveMins = $lateHours = $lateMins = 0;
            foreach ($employees as $employee) {

                $attendances['name'] = !empty($employee->users) ? $employee->users->name : '';

                foreach ($dates as $date) {
                    $dateFormat = $year . '-' . $month . '-' . $date;

                    if ($dateFormat <= date('Y-m-d')) {
                        $employeeAttendance = Attendance::where('employee_id', $employee->user_id)->where('date', $dateFormat)->first();

                        if (!empty($employeeAttendance) && $employeeAttendance->status == 'Present') {
                            $attendanceStatus[$date] = 'P';
                            $totalPresent            += 1;

                            if ($employeeAttendance->overtime > 0) {
                                $ovetimeHours += date('h', strtotime($employeeAttendance->overtime));
                                $overtimeMins += date('i', strtotime($employeeAttendance->overtime));
                            }

                            if ($employeeAttendance->early_leaving > 0) {
                                $earlyleaveHours += date('h', strtotime($employeeAttendance->early_leaving));
                                $earlyleaveMins  += date('i', strtotime($employeeAttendance->early_leaving));
                            }

                            if ($employeeAttendance->late > 0) {
                                $lateHours += date('h', strtotime($employeeAttendance->late));
                                $lateMins  += date('i', strtotime($employeeAttendance->late));
                            }
                        } elseif (!empty($employeeAttendance) && $employeeAttendance->status == 'Leave') {
                            $attendanceStatus[$date] = 'L';
                            $totalLeave              += 1;
                        } else {
                            $attendanceStatus[$date] = '';
                        }
                    } else {
                        $attendanceStatus[$date] = '';
                    }
                }
                $attendances['status'] = $attendanceStatus;
                $employeesAttendance[] = $attendances;
            }

            $totalOverTime   = $ovetimeHours + ($overtimeMins / 60);
            $totalEarlyleave = $earlyleaveHours + ($earlyleaveMins / 60);
            $totalLate       = $lateHours + ($lateMins / 60);

            $data['totalOvertime']   = $totalOverTime;
            $data['totalEarlyLeave'] = $totalEarlyleave;
            $data['totalLate']       = $totalLate;
            $data['totalPresent']    = $totalPresent;
            $data['totalLeave']      = $totalLeave;
            $data['curMonth']        = $curMonth;

            return view('report.attendance', compact('employeesAttendance', 'department', 'dates', 'data'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function exportCsv($filter_month, $department)
    {

        $data['department'] = __('All');
        $employees = User::select('users.*', 'employees.department')->where('type', 'employee')->join('employees', 'employees.user_id', '=', 'users.id')->where('users.created_by', \Auth::user()->creatorId());

        if ($department != 0) {

            $employees->where('department', $department);
            $data['department'] = !empty(Department::find($department)) ? Department::find($department)->name : '';
        }

        $employees = $employees->get()->pluck('name', 'id');


        $currentdate = strtotime($filter_month);
        $month       = date('m', $currentdate);
        $year        = date('Y', $currentdate);
        $data['curMonth']    = date('M-Y', strtotime($filter_month));


        $fileName =  $data['curMonth'] . ' ' . __('Attendance Report of') . ' ' . $data['department'] . ' ' . __('Department') . ' ' . '.csv';


        $num_of_days = date('t', mktime(0, 0, 0, $month, 1, $year));
        for ($i = 1; $i <= $num_of_days; $i++) {
            $dates[] = str_pad($i, 2, '0', STR_PAD_LEFT);
        }

        foreach ($employees as $id => $employee) {
            $attendances['name'] = $employee;

            foreach ($dates as $date) {

                $dateFormat = $year . '-' . $month . '-' . $date;

                if ($dateFormat <= date('Y-m-d')) {
                    $employeeAttendance = Attendance::where('employee_id', $id)->where('date', $dateFormat)->first();

                    if (!empty($employeeAttendance) && $employeeAttendance->status == 'Present') {
                        $attendanceStatus[$date] = 'P';
                    } elseif (!empty($employeeAttendance) && $employeeAttendance->status == 'Leave') {
                        $attendanceStatus[$date] = 'A';
                    } else {
                        $attendanceStatus[$date] = '-';
                    }
                } else {
                    $attendanceStatus[$date] = '-';
                }
                $attendances[$date] = $attendanceStatus[$date];
            }

            $employeesAttendance[] = $attendances;
        }

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
        );

        $emp = array(
            'employee',
        );

        $columns = array_merge($emp, $dates);

        $callback = function () use ($employeesAttendance, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($employeesAttendance as $attendance) {
                fputcsv($file, str_replace('"', '', array_values($attendance)));
            }


            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function ledgerSummary(Request $request)
    {

        $accounts = ChartOfAccount::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        // dd($account);
        $accounts->prepend('Select Accounts', '');

        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date;
            $end   = $request->end_date;
        } else {
            $start = date('Y-m-01');
            $end   = date('Y-m-t');
        }

        if (!empty($request->account)) {
            $account = ChartOfAccount::find($request->account);
        } else {
            $account = ChartOfAccount::where('created_by', \Auth::user()->creatorId())->first();
        }


        $journalItems = JournalItem::select('journal_entries.journal_id', 'journal_entries.date as transaction_date', 'journal_items.*')->leftjoin('journal_entries', 'journal_entries.id', 'journal_items.journal')->where('journal_entries.created_by', '=', \Auth::user()->creatorId())->where('account', !empty($account) ? $account->id : 0);
        $journalItems->where('date', '>=', $start);
        $journalItems->where('date', '<=', $end);
        $journalItems = $journalItems->get();

        $balance = 0;
        $debit   = 0;
        $credit  = 0;
        foreach ($journalItems as $item) {
            if ($item->debit > 0) {
                $debit += $item->debit;
            } else {
                $credit += $item->credit;
            }

            $balance = $credit - $debit;
        }

        $filter['balance']        = $balance;
        $filter['credit']         = $credit;
        $filter['debit']          = $debit;
        $filter['startDateRange'] = $start;
        $filter['endDateRange']   = $end;


        return view('report.ledger_summary', compact('filter', 'journalItems', 'account', 'accounts'));
    }
    public function balanceSheet(Request $request)
    {
      
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date;
            $end   = $request->end_date;
        } else {
            $start = date('Y-m-01');
            $end   = date('Y-m-t');
        }

        $types = ChartOfAccountType::get();
        $chartAccounts = [];
        if (!empty(\Auth::user()->creatorId())) {
            foreach ($types as $type) {
                $subTypes = ChartOfAccountSubType::where('type', $type->id)->get();
                
                $subTypeArray = [];
                
                foreach ($subTypes as $subType) {
                    $accounts = ChartOfAccount::where('created_by', \Auth::user()->creatorId())->where('type', $type->id)->where('sub_type', $subType->id)->get();
                    
                    $accountArray = [];
                    foreach ($accounts as $account) {
                        
                        $journalItem = JournalItem::select(\DB::raw('sum(credit) as totalCredit'), \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) - sum(debit) as netAmount'))->where('account', $account->id);
                        $journalItem->where('created_at', '>=', $start);
                        $journalItem->where('created_at', '<=', $end);
                        $journalItem          = $journalItem->first();
                        $data['account_name'] = $account->name;
                        $data['totalCredit']  = $journalItem->totalCredit;
                        $data['totalDebit']   = $journalItem->totalDebit;
                        $data['netAmount']    = $journalItem->netAmount;
                        $accountArray[]       = $data;
                    }
                    $subTypeData['subType'] = $subType->name;
                    $subTypeData['account'] = $accountArray;
                    $subTypeArray[]         = $subTypeData;
                }

                $chartAccounts[$type->name] = $subTypeArray;
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $filter['startDateRange'] = $start;
        $filter['endDateRange']   = $end;


        return view('report.balance_sheet', compact('filter', 'chartAccounts'));
    }


    public function trialBalanceSummary(Request $request)
    {

        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date;
            $end   = $request->end_date;
        } else {
            $start = date('Y-m-01');
            $end   = date('Y-m-t');
        }

        $journalItem = JournalItem::select('chart_of_accounts.name', \DB::raw('sum(credit) as totalCredit'), \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) - sum(debit) as netAmount'));
        $journalItem->leftjoin('journal_entries', 'journal_entries.id', 'journal_items.journal');
        $journalItem->leftjoin('chart_of_accounts', 'journal_items.account', 'chart_of_accounts.id');
        $journalItem->where('journal_items.created_at', '>=', $start);
        $journalItem->where('journal_items.created_at', '<=', $end);
        $journalItem->groupBy('account');
        $journalItem = $journalItem->get()->toArray();

        $filter['startDateRange'] = $start;
        $filter['endDateRange']   = $end;

        return view('report.trial_balance', compact('filter', 'journalItem'));
    }

    public function productStock(Request $request)
    {
        $stocks = StockReport::where('created_by', '=', \Auth::user()->creatorId())->with('item')->get();
        return view('report.item_stock_report', compact('stocks'));
    }

    // public function TaskReportExport()
    // {
    //     $name = 'task_report_' . date('Y-m-d i:h:s');
    //     $data = Excel::download(new task_reportExport($id), $name . '.xlsx'); ob_end_clean();

    //     return $data;
    // }

    public function TaskReportExport()
    {
        $name = 'task_report' . date('Y-m-d i:h:s');
        // $data = Excel::download(new task_reportExport(), $name . '.xlsx');
        $data = Excel::download(new task_reportExport(), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }
    public function InvoiceReportExport()
    {
        $name = 'invoice_report' . date('Y-m-d i:h:s');
        $data = Excel::download(new InvoiceExport(), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }
    public function LeadExport()
    {
        $name = 'lead_report' . date('Y-m-d i:h:s');
        $data = Excel::download(new LeadExport(), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }
    public function DealExport()
    {
        
        $name = 'deal_report' . date('Y-m-d i:h:s');
        $data = Excel::download(new DealExport(), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }
    public function LeaveReportExport()
    {
        $name = 'leave_report' . date('Y-m-d i:h:s');
        $data = Excel::download(new LeaveReportExport(), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }
    public function TimelogExport()
    {
        $name = 'time_report' . date('Y-m-d i:h:s');
        $data = Excel::download(new TimelogExport(), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }
    public function FinanceExport()
    {
        $name = 'finance_report' . date('Y-m-d i:h:s');
        $data = Excel::download(new FinanceExport(), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }

    public function ClientExport()
    {
        $name = 'client_report' . date('Y-m-d i:h:s');
        $data = Excel::download(new ClientExport(), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }
    public function EstimateExport()
    {
        $name = 'estimate_report' . date('Y-m-d i:h:s');
        $data = Excel::download(new EstimateExport(), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }
    public function StockReportExport()
    {
        $name = 'stock_report' . date('Y-m-d i:h:s');
        $data = Excel::download(new StockReportExport(), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }
}

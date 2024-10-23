<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Mail\LeaveActionSend;
use App\Models\User;
use App\Models\Utility;
use App\Models\Employee;
use App\Exports\LeaveReportExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\GoogleCalendar\Event as GoogleEvent;

class LeaveController extends Controller
{

    public function index(Request $request)
    {
        $usr = \Auth::user();
        if ($usr->type == 'company' || $usr->type == 'employee') {
            $employees   =  User::where('type', 'employee')->where('created_by', $usr->creatorId())->get()->pluck('name', 'id');
            $employees->prepend('Select Employee', '');

            if ($usr->type == 'company') {
                $leaves  =   Leave::where('created_by', '=', $usr->creatorId());
            } else {
                $leaves  =   Leave::where('employee_id', '=', $usr->id);
            }

            if (!empty($request->employee)) {
                $leaves->where('employee_id', $request->employee);
            }

            if (!empty($request->start_date)) {
                $leaves->where('start_date', '>=', $request->start_date);
            }

            if (!empty($request->end_date)) {
                $leaves->where('end_date', '<=', $request->end_date);
            }

            $leaves = $leaves->with('user')->with('leaveType')->get();

            return view('leave.index', compact('leaves', 'employees'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        $usr = \Auth::user();
        if ($usr->type == 'company' || $usr->type == 'employee') {
            $leaveTypes         = LeaveType::where('created_by', '=', $usr->creatorId())->get();
            $leaveTypesDays     = LeaveType::where('created_by', '=', $usr->creatorId())->get();

            $employees          = User::where('created_by', '=', $usr->creatorId())->where('type', 'employee')->get()->pluck('name', 'id');
            $employees->prepend('Select Employee', '');

            return view('leave.create', compact('employees', 'leaveTypes', 'leaveTypesDays'));
        }
    }


    public function store(Request $request)
    {
        // dd($request);
        $usr    = \Auth::user();
        if ($usr->type == 'company' || $usr->type == 'employee') {
            if ($usr->type == 'company') {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'employee_id'       => 'required',
                        'leave_type'        => 'required',
                        'start_date'        => 'required',
                        'end_date'          => 'required|date|after_or_equal:start_date',
                        'leave_reason'      => 'required',
                        'remark'            => 'required',
                    ]
                );
            } else {

                $validator = \Validator::make(
                    $request->all(),
                    [
                        'leave_type'        => 'required',
                        'start_date'        => 'required',
                        'end_date'          => 'required|date|after_or_equal:start_date',
                        'leave_reason'      => 'required',
                        'remark'            => 'required',
                    ]
                );
            }
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $employees      =    User::where('type', 'employee')->where('created_by', $usr->creatorId())->get()->pluck('name', 'id');

            $leave          =    new Leave();
            if ($usr->type == 'employee') {
                $leave->employee_id = $usr->id;
            } else {
                $leave->employee_id = $request->employee_id;
            }

            $leave->leave_type          =       $request->leave_type;
            $leave->applied_on          =       date('Y-m-d');
            $leave->start_date          =       $request->start_date;
            $leave->end_date            =       $request->end_date;
            $leave->total_leave_days    =       0;
            $leave->leave_reason        =       $request->leave_reason;
            $leave->remark              =       $request->remark;
            $leave->status              =       'Pending';
            $leave->created_by          =       $usr->creatorId();

            $leave->save();

            if ($request->get('synchronize_type') == 'google_calender') {
                $type = 'leave';
                $request1                =      new GoogleEvent();
                $request1->title         =      $employees[$request->employee_id];
                $request1->start_date    =      $request->start_date;
                $request1->end_date      =      $request->end_date;
                Utility::addCalendarData($request1, $type);
            }

            return redirect()->route('leave.index')->with('success', __('Leave  successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show(Leave $leave)
    {
        //
    }


    public function edit(Leave $leave)
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee') {
            $employees      = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'employee')->get()->pluck('name', 'id');
            $employees->prepend('Select Employee', '');
            $leaveTypes     = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();

            return view('leave.edit', compact('leave', 'employees', 'leaveTypes'));
        }
    }


    public function update(Request $request, Leave $leave)
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'leave_type'        =>  'required',
                    'start_date'        =>  'required',
                    'end_date'          =>  'required',
                    'leave_reason'      =>  'required',
                    'remark'            =>  'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            
            $leave->employee_id         = $request->employee_id;
            $leave->leave_type          = $request->leave_type;
            $leave->start_date          = $request->start_date;
            $leave->end_date            = $request->end_date;
            $leave->total_leave_days    = 0;
            $leave->leave_reason        = $request->leave_reason;
            $leave->remark              = $request->remark;
            $leave->save();
            return redirect()->route('leave.index')->with('success', __('Leave successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Leave $leave)
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee') {
            $leave->delete();
            return redirect()->route('leave.index')->with('success', __('Leave successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function action($id)
    {
        if (\Auth::user()->type == 'company') {
            $leave      =   Leave::find($id);
            $employee   =   User::find($leave->employee_id);
            return view('leave.action', compact('employee', 'leave'));
        }
    }

    public function changeAction(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $leave              = Leave::find($request->leave_id);
            $leave->status      = $request->status;

            if ($leave->status == 'Approve') {
                $startDate               = new \DateTime($leave->start_date);
                $endDate                 = new \DateTime($leave->end_date);
                $total_leave_days        = $startDate->diff($endDate)->days;
                $leave->total_leave_days = $total_leave_days;
                $leave->status           = 'Approve';
            }
            $leave->save();

            $employee       = Employee::where('user_id', $leave->employee_id)->first();
            $setting        = Utility::settings();
            if (isset($settings['twilio_leave_approve_reject_notification']) && $settings['twilio_leave_approve_reject_notification'] == 1) {
                $uArr = [
                    'user_name' => \Auth::user()->name,
                    'status' => $leave->status,
                ];
                Utility::send_twilio_msg('leave_status', $uArr);
            }
            // if(isset($setting['twilio_leave_approve_reject_notification']) && $setting['twilio_leave_approve_reject_notification'] ==1)
            // {
            //      $msg = __("Your leave has been").' '.$leave->status.'.';
            //      Utility::send_twilio_msg($employee->emergency_contact,$msg);
            // }
            return redirect()->route('leave.index')->with('success', __('Leave status successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function jsonCount(Request $request)
    {
        $leave_counts = LeaveType::select(\DB::raw('COALESCE(SUM(leaves.total_leave_days),0) AS total_leave, leave_types.title, leave_types.days,leave_types.id'))->leftjoin(
            'leaves',
            function ($join) use ($request) {
                $join->on('leaves.leave_type', '=', 'leave_types.id');
                $join->where('leaves.employee_id', '=', $request->employee_id);
            }
        )->groupBy('leave_types.id')->get();

        return $leave_counts;
    }

    public function LeaveReportExport()
    {
        $name = 'leave_report' . date('Y-m-d i:h:s');
        $data = Excel::download(new LeaveReportExport(), $name . '.xlsx');
        ob_end_clean();
        return $data;
    }

    public function calendar(Request $request)
    {
        $employees = User::where('type', 'employee')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $employees->prepend('Select Employee', '');

        if (\Auth::user()->type == 'company') {
            $leaves = Leave::where('created_by', '=', \Auth::user()->creatorId());
        } else {
            $leaves = Leave::where('employee_id', '=', \Auth::user()->id);
        }
        if (!empty($request->employee)) {
            $leaves->where('employee_id', $request->employee);
        }

        if (!empty($request->start_date)) {
            $leaves->where('start_date', '>=', $request->start_date);
        }

        if (!empty($request->end_date)) {
            $leaves->where('end_date', '<=', $request->end_date);
        }

        $leaves = $leaves->get();

        return view('leave.calendar', compact('leaves', 'employees'));
    }

    public function get_holiday_data(Request $request)
    {
        $usr            =   \Auth::user();
        $arrayJson      =   [];
        if ($request->get('calender_type') == 'google_calender') {

            $type           =   'leave';
            $arrayJson      =   Utility::getCalendarData($type);
            return $arrayJson;
        } else {
            // $data = localMeeting::get();
            $employees      =    User::where('type', 'employee')->where('created_by', $usr->creatorId())->get()->pluck('name', 'id');
            // $employees->prepend('Select Employee', '');
            if ($usr->type == 'company') {
                $leaves         =    Leave::where('created_by', '=', $usr->creatorId())->get();
            } else {
                $leaves         =    Leave::where('employee_id', '=', $usr->id)->get();
            }

            foreach ($leaves as $val) {
                $end_date       =   date_create($val->end_date);
                date_add($end_date, date_interval_create_from_date_string("1 days"));
                $arrayJson[]    = [
                    "id"            =>   $val->id,
                    "title"         =>   $employees[$val->employee_id],
                    "start"         =>   $val->start_date,
                    "end"           =>   date_format($end_date, "Y-m-d H:i:s"),
                    "className"     =>   'event-primary',
                    "textColor"     =>   '#FFF',
                    "allDay"        =>   true,
                    "url"           =>   route('leave.edit', $val['id']),
                ];
            }
            // dd($arrayJson);
            return $arrayJson;
        }
    }
}
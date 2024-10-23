<?php
namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\event as localevent;
use App\Models\User;
use Spatie\GoogleCalendar\Event as GoogleEvent;
use Illuminate\Http\Request;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{

    public function index()
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee') {
            $transdate = date('Y-m-d', time());
            $events    = localevent::where('created_by', \Auth::user()->creatorId())->get();
            $arrEvents = [];
            $events_current_month =  localevent::whereMonth('start_date', date('m'))->whereYear('start_date', date('Y'))->get(['name', 'start_date', 'end_date' , 'start_time','end_time']);
            foreach ($events as $event) {
                $arr['id']        = $event['id'];   
                $arr['title']     = $event['name'];
                $arr['start']     = $event['start_date'];
                $arr['end']       = $event['end_date'];
                $arr['className'] = $event['color'];
                $arr['url']       = route('event.edit', $event['id']);
                $arrEvents[] = $arr;
            }

            $arrEvents = str_replace('"[', '[', str_replace(']"', ']', json_encode($arrEvents)));

            return view('event.index', compact('arrEvents', 'events', 'transdate', 'events_current_month'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $departments->prepend('All', 0);

        return view('event.create', compact('departments'));
    }

    public function store(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'department' => 'required',
                    'employee' => 'required',
                    'start_date' => 'required',
                    'start_time' => 'required',
                    'end_date' => 'required|date|after_or_equal:start_date',
                    'end_time' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $event              = new localevent();
            $event->name        = $request->name;
            $event->where       = $request->where;
            $event->department  = implode(',', $request->department);
            $event->employee    = implode(',', $request->employee);
            $event->start_date  = $request->start_date;
            $event->start_time  = $request->start_time;
            $event->end_date    = $request->end_date;
            $event->end_time    = $request->end_time;
            $event->color       = $request->color;
            $event->description = $request->description;
            $event->created_by  = \Auth::user()->creatorId();

            $event->save();

            // ==========get Department Name========

            $dept = [];
            foreach ($request->department as $department) {
                if ($department == 0) {
                    $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
                    $dept = $departments;
                }
            }
            $department_name = implode(',', $dept);
            // ==========get Department Name========

            // ==========get Employee Name========
            $emp = [];
            foreach ($request->employee as $employee) {
                if ($employee == 0) {
                    $employees = User::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
                    $emp = $employees;
                }
            }

            $employee_name = implode(',', $emp);
            // ==========get Employee Name end========

            // $settings  = Utility::settings();
            // if (isset($settings['event_create_notification']) && $settings['event_create_notification'] == 1) {
            //     $msg = $request->name . ' ' . __('for ') . $department_name . ' ' . __('for ') . $employee_name . __(' from ') . $request->start_date . ' ' . __('to ') . $request->end_date . '.';
            //     Utility::send_slack_msg($msg);
            //}
            //$settings  = Utility::settings(\Auth::user()->creatorId());

            //=== slack Notification========

            $settings  = Utility::settings();
            if (isset($settings['event_create_notification']) && $settings['event_create_notification'] == 1) {
                $uArr = [
                    'event_title' => $event->name,
                    'department_name' =>  $department_name,
                    'start_date' => $event->end_date,
                    'end_date' => $event->end_date,
                ];
                Utility::send_slack_msg('new_event', $uArr);
            }

            if (isset($settings['telegram_event_create_notification']) && $settings['telegram_event_create_notification'] == 1) {
                $uArr = [
                    'event_title' => $event->name,
                    'department_name' =>  $department_name,
                    'start_date' => $event->end_date,
                    'end_date' => $event->end_date,
                ];
                Utility::send_telegram_msg('new_event', $uArr);
            }
            $employee = Employee::where('user_id', $request->employee)->first();
            if (isset($settings['twilio_event_create_notification']) && $settings['twilio_event_create_notification'] == 1) {
                $uArr = [
                    'event_title' => $event->name,
                    'department_name' =>  $department_name,
                    'start_date' => $event->end_date,
                    'end_date' => $event->end_date,
                ];
                Utility::send_twilio_msg('new_event', $uArr);
            }

            if ($request->get('synchronize_type') == 'google_calender') {
                $type = 'event';
                $request1 = new GoogleEvent();
                $request1->title = $request->name;
                $request1->start_date = $request->start_date;
                $request1->end_date = $request->end_date;

                Utility::addCalendarData($request1, $type);
            }
            //webhook
            $module = "New event";
            $webhook = Utility::webhookSetting($module, \Auth::user()->creatorId());
            if ($webhook) {
                $parameter = json_encode($event);

                // 1 parameter is URL , 2  (event Data) parameter is data , 3 parameter is method
                $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                if ($status == true) {
                    return redirect()->back()->with('success', __('event successfully created.'));
                } else {
                    return redirect()->back()->with('error', __('event call failed.'));
                }
            }
            return redirect()->route('event.index')->with('success', __('Event successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show(localevent $event)
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee') {
            $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments->prepend('All', 0);

            $event->department = explode(',', $event->department);
            $event->employee   = explode(',', $event->employee);

            $dep = [];
            foreach ($event->department as $department) {

                if ($department == 0) {
                    $dep[] = 'All Department';
                } else {
                    $departments = Department::find($department);
                    $dep[]       = $departments->name;
                }
            }

            $emp = [];
            foreach ($event->employee as $employee) {
                if ($employee == 0) {
                    $emp[] = 'All Employee';
                } else {
                    $employees = User::find($employee);
                    $emp[]     = $employees->name;
                }
            }


            return view('event.show', compact('event', 'dep', 'emp'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function edit(localevent $event)
    {
        $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $departments->prepend('All', 0);

        return view('event.edit', compact('departments', 'event'));
    }


    public function update(Request $request, localevent $event)
    {
        if (\Auth::user()->type == 'company') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'department' => 'required',
                    'employee' => 'required',
                    'start_date' => 'required',
                    'start_time' => 'required',
                    'end_date' => 'required|date|after_or_equal:start_date',
                    'end_time' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }


            $event->name        = $request->name;
            $event->where       = $request->where;
            $event->start_date  = $request->start_date;
            $event->start_time  = $request->start_time;
            $event->end_date    = $request->end_date;
            $event->end_time    = $request->end_time;
            $event->color       = $request->color;
            $event->description = $request->description;

            $event->save();

            return redirect()->route('event.index')->with('success', __('Event successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(localevent $event)
    {
        //
    }

    public function getEmployee(Request $request)
    {

        if (in_array('0', $request->department)) {

            $employees = Employee::where('created_by',\Auth::user()->creatorId())->get();
        } else {
            $employees = Employee::whereIn('department', $request->department)->where('created_by',\Auth::user()->creatorId())->get();
        }
        $users = [];
        foreach ($employees as $employee) {
            if (!empty($employee->users)) {
                $users[$employee->users->id] = $employee->users->name;
            }
        }

        return response()->json($users);
    }

    public function get_event_data(Request $request)
    {

        $arrayJson = [];
        if ($request->get('calender_type') == 'google_calender') {

            $type = 'event';
            $arrayJson =  Utility::getCalendarData($type);
        } else {
            $data = localevent::get();


            foreach ($data as $val) {

                $end_date = date_create($val->end_date);
                date_add($end_date, date_interval_create_from_date_string("1 days"));
                $arrayJson[] = [
                    "id" => $val->id,
                    "title" => $val->name,
                    "start" => $val->start_date,
                    "end" => date_format($end_date, "Y-m-d H:i:s"),
                    "className" => $val->color,
                    "allDay" => true,
                    "url" => route('event.edit', $val['id']),
                ];
            }
        }

        return $arrayJson;
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Meeting as localMeeting;
use App\Models\User;
use App\Exports\MeetingExport;
use App\Models\UserDefualtView;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;
use Spatie\GoogleCalendar\Event as GoogleEvent;
use Termwind\Components\Dd;
use App\Models\Employee;

class MeetingController extends Controller
{

    public function index(Request $request)
    {
        $usr = \Auth::user();
        if ($usr->type == 'company' || $usr->type == 'employee') {

            $departments = Department::where('created_by', '=', $usr->creatorId())->get()->pluck('name', 'id');
            $departments->prepend('Select Department', 0);

            $designations = Designation::where('created_by', '=', $usr->creatorId())->get()->pluck('name', 'id');
            $designations->prepend('Select Designation', 0);

            if ($usr->type == 'company') {
                $meetings       =   localMeeting::where('created_by', '=', $usr->creatorId());
            } else {
                $emloyee        =   Employee::where('user_id', $usr->id)->first();
                $meetings       =   localMeeting::where('created_by', '=', $usr->creatorId())->where('department', $emloyee->department)->where('designation', $emloyee->designation)->orWhere('department', 0)->orWhere('designation', 0);
            }

            if (!empty($request->department)) {
                $meetings->where('department', $request->department);
            }
            if (!empty($request->designation)) {
                $meetings->where('designation', $request->designation);
            }
            if (!empty($request->start_date)) {
                $meetings->where('date', '>=', $request->start_date);
            }
            if (!empty($request->end_date)) {
                $meetings->where('date', '<=', $request->end_date);
            }
            $meetings            =   $meetings->with('departments')->with('designations')->get();


            $defualtView         =   new UserDefualtView();
            $defualtView->route  =   \Request::route()->getName();
            $defualtView->module =   'meeting';
            $defualtView->view   =   'list';
            User::userDefualtView($defualtView);

            return view('meeting.index', compact('meetings', 'departments', 'designations'));
        }
    }


    public function create()
    {
        $departments        = Department::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $departments->prepend('All', 0);
        $designations       = Designation::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $designations->prepend('All', 0);

        return view('meeting.create', compact('departments', 'designations'));
    }

    public function store(Request $request)
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'title'  => 'required',
                    'date'   => 'required',
                    'time'   => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $meeting                    =    new localMeeting();
            $meeting->department        =    $request->department;
            $meeting->designation       =    $request->designation;
            $meeting->title             =    $request->title;
            $meeting->date              =    $request->date;
            $meeting->time              =    $request->time;
            $meeting->notes             =    $request->notes;
            $meeting->created_by        =    \Auth::user()->creatorId();
            $meeting->save();

            $department_name = Department::where('created_by', \Auth::user()->creatorId())->where('id', $request->department)->first();
            $settings  = Utility::settings();

            // if(isset($settings['meeting_create_notification']) && $settings['meeting_create_notification'] ==1){
            //     $msg = $request->title." ".__("meeting created for department")." ".$department_name->name." from ".$request->date.' at '.$request->time.'.';
            //     //dd($msg);
            //     Utility::send_slack_msg($msg);
            // }
            if (isset($settings['meeting_create_notification']) && $settings['meeting_create_notification'] == 1) {
                $uArr = [
                    'title' => $meeting->title,
                    'date' => $meeting->date,
                ];
                Utility::send_slack_msg('new_meeting', $uArr);
            }
            if (isset($settings['telegram_meeting_create_notification']) && $settings['telegram_meeting_create_notification'] == 1) {
                $uArr = [
                    'title' => $meeting->title,
                    'date' => $meeting->date,
                ];
                Utility::send_telegram_msg('new_meeting', $uArr);
            }
            // if(isset($settings['telegram_meeting_create_notification']) && $settings['telegram_meeting_create_notification'] ==1){
            //     $resp = $request->title." ".__("meeting created for department")." ".$department_name->name." from ".$request->date.' at '.$request->time.'.';
            //     Utility::send_telegram_msg($resp);
            // }

            if ($request->get('synchronize_type') == 'google_calender') {

                $type = 'meeting';
                $request1               = new GoogleEvent();
                $request1->title        = $request->title;
                $request1->start_date   = $request->date;
                $request1->end_date = $request->date;

                Utility::addCalendarData($request1, $type);
            }

            //webhook
            $module = "New Meeting";

            $webhook = Utility::webhookSetting($module);
            if ($webhook) {
                $parameter = json_encode($meeting);

                // 1 parameter is URL , 2  (Meeting Data) parameter is data , 3 parameter is method
                $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                if ($status == true) {
                    return redirect()->back()->with('success', __('Meeting successfully created.'));
                } else {
                    return redirect()->back()->with('error', __('Meeting call failed.'));
                }
            }

            return redirect()->route('meeting.index')->with('success', __('Meeting  successfully created.'));
        }
    }


    public function show(localMeeting $meeting)
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee') {
            $departments                 = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments->prepend('All', 0);
            $meeting->department         = explode(',', $meeting->department);
            $meeting->designation        = explode(',', $meeting->designation);

            $dep = [];
            foreach ($meeting->department as $department) {
                if ($department == 0) {
                    $dep[] = 'All Department';
                } else {
                    $departments    =   Department::find($department);
                    $dep[]          =   $departments->name;
                }
            }
            $des = [];
            foreach ($meeting->designation as $designation) {
                if ($designation == 0) {
                    $des[] = 'All Designation';
                } else {
                    $designations = Designation::find($designation);
                    $des[]        = $designations->name;
                }
            }

            return view('meeting.show', compact('meeting', 'dep', 'des'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function edit(localMeeting $meeting)
    {
        $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $departments->prepend('All', 0);
        $designations = Designation::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $designations->prepend('All', 0);

        return view('meeting.edit', compact('departments', 'designations', 'meeting'));
    }


    public function update(Request $request, localMeeting $meeting)
    {
        if (\Auth::user()->type == 'company') {
            $validator = \Validator::make(
                $request->all(),
                [

                    'title' => 'required',
                    'date' => 'required',
                    'time' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }


            $meeting->department  = $request->department;
            $meeting->designation = $request->designation;
            $meeting->title       = $request->title;
            $meeting->date        = $request->date;
            $meeting->time        = $request->time;
            $meeting->notes       = $request->notes;
            $meeting->save();

            return redirect()->route('meeting.index')->with('success', __('Meeting  successfully updated.'));
        }
    }


    public function destroy(localMeeting $meeting)
    {
        if (\Auth::user()->type == 'company') {
            $meeting->delete();
            return redirect()->route('meeting.index')->with('success', __('Meeting successfully deleted.'));
        }
    }


    public function calendar(Request $request)
    {
        $usr    =   \Auth::user();
        if ($usr->type == 'company' || $usr->type == 'employee') {
            $transdate      =   date('Y-m-d', time());

            if ($usr->type == 'company') {
                $meetings       =   localMeeting::where('created_by', '=', $usr->creatorId())->get();
            } else {
                $emloyee        =   Employee::where('user_id', $usr->id)->first();
                $meetings       =   localMeeting::where('created_by', '=', $usr->creatorId())->where('department', $emloyee->department)->where('designation', $emloyee->designation)->orWhere('department', 0)->orWhere('designation', 0)->get();;
            }

            $arrMeeting              =   [];
            foreach ($meetings as $meet) {
                $arr['id']          =   $meet['id'];
                $arr['title']       =   $meet['title'];
                $arr['start']       =   $meet['date'];
                $arr['className']   =   'event-danger';
                $arr['url']         =   route('meeting.show', $meet['id']);
                $arrMeeting[]       =   $arr;
            }

            $arrMeeting                 =       str_replace('"[', '[', str_replace(']"', ']', json_encode($arrMeeting)));
            $defualtView                =       new UserDefualtView();
            $defualtView->route         =       \Request::route()->getName();
            $defualtView->module        =       'meeting';
            $defualtView->view          =       'calendar';


            if ($usr->type == 'company') {
                $meeting_current_month          =   localMeeting::whereMonth('date', date('m'))->whereYear('date', date('Y'))->get(['title', 'date']);
            } else {
                $meeting_current_month          =   localMeeting::where('department', $emloyee->department)->where('designation', $emloyee->designation)->whereMonth('date', date('m'))->whereYear('date', date('Y'))->orWhere('department', 0)->orWhere('designation', 0)->get(['title', 'date']);
            }

            User::userDefualtView($defualtView);
            return view('meeting.calendar', compact('arrMeeting', 'transdate', 'meeting_current_month'));
        }
    }

    public function export()
    {
        $name   =   'meeting' . date('Y-m-d i:h:s');
        $data   =   Excel::download(new MeetingExport(), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }

    public function get_holiday_data(Request $request)
    {
        $usr         =   \Auth::user();
        $arrayJson   = [];

        if ($request->get('calender_type') == 'google_calender') {
            $type           = 'meeting';
            $arrayJson      = Utility::getCalendarData($type);
        } else {

            if ($usr->type == 'company') {
                $data           =   localMeeting::where('created_by', '=', $usr->creatorId())->get();
            } else {
                $emloyee        =   Employee::where('user_id', $usr->id)->first();
                $data           =   localMeeting::where('department', $emloyee->department)->where('designation', $emloyee->designation)->orWhere('department', 0)->orWhere('designation', 0)->get();
            }

            foreach ($data as $val) {
                $end_date           = date_create($val->end_date);
                date_add($end_date, date_interval_create_from_date_string("1 days"));
                $arrayJson[] = [
                    "id"            => $val->id,
                    "title"         => $val->title,
                    "start"         => $val->date,
                    "end"           => $val->date,
                    "className"     => 'event-primary',
                    "textColor"     => '#FFF',
                    "allDay"        => true,
                    "url"           => route('meeting.edit', $val['id']),
                ];
            }
        }

        return $arrayJson;
    }
}

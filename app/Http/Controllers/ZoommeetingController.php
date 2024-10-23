<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\User;
use App\Models\Utility;
use App\Models\Zoommeeting as localZoommeeting;
use App\Models\UserDefualtView;
use Illuminate\Http\Request;
use App\Traits\ZoomMeetingTrait;
use Spatie\GoogleCalendar\Event as GoogleEvent;


class ZoommeetingController extends Controller
{
    use ZoomMeetingTrait;
    const MEETING_TYPE_INSTANT = 1;
    const MEETING_TYPE_SCHEDULE = 2;
    const MEETING_TYPE_RECURRING = 3;
    const MEETING_TYPE_FIXED_RECURRING_FIXED = 8;   
    const MEETING_URL="https://api.zoom.us/v2/";


    public function index()
    {
        if(\Auth::user()->type == 'client'){
            $meetings = localZoommeeting::where('client_id',\Auth::user()->id)->with('projectName','projectClient')->get();

        }else if (\Auth::user()->type == 'employee'){

            $meetings = localZoommeeting::where('employee',\Auth::user()->id)->with('projectName','projectClient')->get();
        }
        else{
            $meetings = localZoommeeting::where('created_by',\Auth::user()->id)->with('projectName','projectClient')->get();
        }
        
       $getUsersData = localZoommeeting::getUsersData();

        return view('zoommeeting.index',compact('meetings','getUsersData'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $project = Project::where('created_by', '=', \Auth::user()->creatorId())->pluck('title', 'id');
        $employees = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'employee')->get()->pluck('name', 'id');
        return view('zoommeeting.create',compact('employees','project'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'title' => 'required',
                                   'project_id' => 'required',
                                   'start_date' => 'required',
                                ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('zoommeeting.index')->with('error', $messages->first());
            }

            $data['title'] = $request->title;
            $data['start_time'] = date('y:m:d H:i:s',strtotime($request->start_date));
            $data['duration'] = (int)$request->duration;
            $data['password'] = $request->password;
            $data['host_video'] = 0;
            $data['participant_video'] = 0;
            $meeting_create = $this->createmitting($data);
            \Log::info('Meeting');
            \Log::info((array)$meeting_create);
            if(isset($meeting_create['success']) &&  $meeting_create['success'] == true)
            {
                $meeting_id = isset($meeting_create['data']['id'])?$meeting_create['data']['id']:0;
                $start_url = isset($meeting_create['data']['start_url'])?$meeting_create['data']['start_url']:'';
                $join_url = isset($meeting_create['data']['join_url'])?$meeting_create['data']['join_url']:'';
                $status = isset($meeting_create['data']['status'])?$meeting_create['data']['status']:'';


                $client = Project::where('id' , $request->project_id)->first();

                $zoommeeting              = new localZoommeeting();
                $zoommeeting->title       = $request->title;
                $zoommeeting->meeting_id  = $meeting_id;
                $zoommeeting->project_id  = $request->project_id;
                $zoommeeting->employee    = implode(',', [$request->employee] ?? '');

                $zoommeeting->start_date  = date('y:m:d H:i:s',strtotime($request->start_date));
                $zoommeeting->duration    = $request->duration;
                $zoommeeting->start_url   = $start_url;
                $zoommeeting->client_id   = isset($request->client_id) ? $client->client : 0;
                $zoommeeting->join_url    = $join_url;
                $zoommeeting->status      = $status;
                $zoommeeting->created_by  = \Auth::user()->creatorId();

                $zoommeeting->save();


                if ($request->get('synchronize_type') == 'google_calender') {

                    $type = 'zoom_meeting';
                    $request1 = new GoogleEvent();
                    $request1->title = $request->title;
                    $request1->start_date = $request->start_date;
                    $request1->end_date = $request->start_date;

                    Utility::addCalendarData($request1, $type);
                }

                return redirect()->route('zoommeeting.index')->with('success', __('Zoom Meeting successfully created.'));
            }

            else
            {
                return redirect()->back()->with('error', __('Meeting not created.'));
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Zoommeeting  $zoommeeting
     * @return \Illuminate\Http\Response
     */
    public function show(localZoommeeting $zoommeeting)
    {
        $meeting = localZoommeeting::where('created_by', '=', \Auth::user()->creatorId())->first();
        return view('zoommeeting.show',compact('meeting'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Zoommeeting  $zoommeeting
     * @return \Illuminate\Http\Response
     */
    public function edit(localZoommeeting $zoommeeting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Zoommeeting  $zoommeeting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, localZoommeeting $zoommeeting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Zoommeeting  $zoommeeting
     * @return \Illuminate\Http\Response
     */
    public function destroy(localZoommeeting $zoommeeting)
    {
        $zoommeeting->delete();
        return redirect()->route('zoommeeting.index')->with('success', __('Meeting successfully deleted.'));
    }

    public function projectwiseuser($id){
        $project = ProjectUser::select('user_id')->where('project_id',$id)->get();

        $users=[];
            foreach($project as $key => $value )
            {
                $user=User::select('id','name')->where('id',$value->user_id)->first();
                if(!empty($user)){
                $users1['id']=$user->id;
                $users1['name']=$user->name;
                $users[]=$users1;
                }
            }

            return \Response::json($users);

        if(!is_null($project)){

            $user = $project->projectUsers()->pluck('name','id');

        }
        return response()->json($user);
    }

    public function statusUpdate(){
        $meetings = localZoommeeting::where('created_by',\Auth::user()->id)->pluck('meeting_id');
        foreach($meetings as $meeting){
            $data = $this->get($meeting);
            if(isset($data['data']) && !empty($data['data'])){
                $meeting = localZoommeeting::where('meeting_id',$meeting)->update(['status'=>$data['data']['status']]);
            }
        }

    }

    //calendar view
    public function calendar(Request $request)
    {

            $transdate = date('Y-m-d', time());

            $meetings = localZoommeeting::where('created_by', '=', \Auth::user()->creatorId())->get();
            $meetings_current_month =  localZoommeeting::whereMonth('start_date', date('m')) ->whereYear('start_date', date('Y')) ->get(['title','meeting_id','start_date']);
            $arrMeeting = [];
            foreach($meetings as $meeting)
            {

                $arr['id']        = $meeting['id'];
                $arr['title']     = $meeting['title'];
                $arr['meeting_id'] = $meeting['meeting_id'];
                 $arr['start'] = $meeting['start_date'];
                $arr['duration'] = $meeting['duration'];
                 $arr['start_url'] = $meeting['start_url'];
                $arr['className'] = 'event-red';
                $arr['url']       = route('zoommeeting.show', $meeting['id']);
                $arrMeeting[] = $arr;

            }
            $arrMeeting = str_replace('"[', '[', str_replace(']"', ']', json_encode($arrMeeting)));

            $defualtView         = new UserDefualtView();
            $defualtView->route  = \Request::route()->getName();
            $defualtView->module = 'Zoom Meeting';
            $defualtView->view   = 'calendar';
            User::userDefualtView($defualtView);

            return view('zoommeeting.calendar', compact('arrMeeting','transdate','meetings_current_month'));

    }

    public function get_holiday_data(Request $request)
    {

        $arrayJson = [];

        if ($request->get('calender_type') == 'google_calender') {

            $type = 'zoom_meeting';
            $arrayJson = Utility::getCalendarData($type);
        } else {
            $data = localZoommeeting::get();

            foreach ($data as $val) {

                $end_date = date_create($val->end_date);
                date_add($end_date, date_interval_create_from_date_string("1 days"));
                $arrayJson[] = [
                    "id" => $val->id,
                    "title" => $val->title,
                    "start" => $val->start_date,
                    "end" => $val->start_date,
                    "className" => 'event-secondary',
                    "textColor" => '#FFF',
                    "allDay" => true,
                    "url" => route('zoommeeting.show',$val['id']),
                ];
            }
        }
        return $arrayJson;
    }
}

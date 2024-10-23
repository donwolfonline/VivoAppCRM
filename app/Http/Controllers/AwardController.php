<?php

namespace App\Http\Controllers;

use App\Models\Award;
use App\Models\AwardType;
use App\Models\Employee;
use App\Models\Mail\AwardSend;
use App\Models\User;
use App\Exports\AwardExport;
use Maatwebsite\Excel\Facades\Excel; 
use Illuminate\Http\Request;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;

class AwardController extends Controller
{
    public function index()
    {
        $usr = \Auth::user();
        if($usr->type == 'company' || $usr->type == 'employee')
        {
            $employees  = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'employee')->get();
            $awardtypes = AwardType::where('created_by', '=', \Auth::user()->creatorId())->get();

            if(\Auth::user()->type == 'employee')
            {
                $emp    = User::where('id', '=', \Auth::user()->id)->first();
                $awards = Award::where('employee_id', '=', $emp->id)->with('awardType')->with('employee')->get();
            }
            else
            {
                $awards = Award::where('created_by', '=', \Auth::user()->creatorId())->with('awardType')->with('employee')->get();
            }

            return view('award.index', compact('awards', 'employees', 'awardtypes'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->type == 'company')
        {
            $employees  = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'employee')->get()->pluck('name', 'id');
            $awardtypes = AwardType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            return view('award.create', compact('employees', 'awardtypes'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {

        if(\Auth::user()->type == 'company')
        {

            $validator = \Validator::make(
                $request->all(), [
                                   'employee_id' => 'required',
                                   'award_type' => 'required',
                                   'date' => 'required',
                                   'gift' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $award              = new Award();
            $award->employee_id = $request->employee_id;
            $award->award_type  = $request->award_type;
            $award->date        = $request->date;
            $award->gift        = $request->gift;
            $award->description = $request->description;
            $award->created_by  = \Auth::user()->creatorId();
            $award->save();

            $employee_name  = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'employee')->where('id', $request->employee_id)->first();
            $awardtype = AwardType::where('created_by', '=', \Auth::user()->creatorId())->where('id', $request->award_type)->first();
            $settings  = Utility::settings();
           
            // if(isset($settings['award_create_notification']) && $settings['award_create_notification'] ==1){
            //     $msg = $awardtype->name." ".__("created for").' '.$employee_name->name.' '.__("for").' '.$request->date.'.';
            //     Utility::send_slack_msg($msg); 
           // }
           if (isset($settings['award_create_notification']) && $settings['award_create_notification'] == 1) {
            $uArr = [
                'award_name'=>$award->gift,
                'employee_name' => $employee_name,
                 'award_date' => $award->date
                ];
            //  dd($uArr);
            Utility::send_slack_msg('new_award', $uArr);
            }
            if (isset($settings['telegram_award_create_notification']) && $settings['telegram_award_create_notification'] == 1) {
                $uArr = [
                    'award_name'=>$award->gift,
                    'employee_name' => $employee_name,
                     'award_date' => $award->date
                    ];
                //  dd($uArr);
                Utility::send_telegram_msg('new_award', $uArr);
                }
            // if(isset($settings['telegram_award_create_notification']) && $settings['telegram_award_create_notification'] ==1){
            //         $resp = $awardtype->name." ".__("created for").' '.$employee_name->name.' '.__("for").' '.$request->date.'.';
            //         Utility::send_telegram_msg($resp);    
            // }
            if (isset($settings['twilio_award_create_notification']) && $settings['twilio_award_create_notification'] == 1) {
                $uArr = [
                    'award_name'=>$award->gift,
                    'employee_name' => $employee_name,
                     'award_date' => $award->date
                    ];
                //  dd($uArr);
                Utility::send_twilio_msg('new_award', $uArr);
                }

             //   $employee = Employee::where('user_id',$request->employee_id)->first();
            // if(isset($settings['twilio_award_create_notification']) && $settings['twilio_award_create_notification'] ==1)
            // {
            //      $message = $awardtype->name." ".__("created for").' '.$employee_name->name.' '.__("for").' '.$request->date.'.';
            //      Utility::send_twilio_msg($employee->emergency_contact,$message);
            // }
            //webhook
            $module = "New award";
            $webhook = Utility::webhookSetting($module);
            if($webhook)
            {
                $parameter = json_encode($award);

                // 1 parameter is URL , 2  (award Data) parameter is data , 3 parameter is method
                $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                if($status == true)
                {
                    return redirect()->back()->with('success', __('award successfully created.'));
                }
                else
                {
                    return redirect()->back()->with('error', __('award call failed.'));
                }
            }
            return redirect()->route('award.index')->with('success', __('Award  successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function edit(Award $award)
    {
        if(\Auth::user()->type == 'company')
        {
            $employees  = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'employee')->get()->pluck('name', 'id');
            $awardtypes = AwardType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            return view('award.edit', compact('award', 'awardtypes', 'employees'));
        }
        else
            {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Award $award)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'employee_id' => 'required',
                                   'award_type' => 'required',
                                   'date' => 'required',
                                   'gift' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $award->employee_id = $request->employee_id;
            $award->award_type  = $request->award_type;
            $award->date        = $request->date;
            $award->gift        = $request->gift;
            $award->description = $request->description;
            $award->save();

            return redirect()->route('award.index')->with('success', __('Award successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Award $award)
    {
        if(\Auth::user()->type == 'company')
        {
            if($award->created_by == \Auth::user()->creatorId())
            {
                $award->delete();

                return redirect()->route('award.index')->with('success', __('Award successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function export()
    {
        $name = 'award' . date('Y-m-d i:h:s');
        $data = Excel::download(new AwardExport(), $name . '.xlsx'); ob_end_clean();

        return $data;
    }
}

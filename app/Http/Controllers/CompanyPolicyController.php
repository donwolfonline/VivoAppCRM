<?php

namespace App\Http\Controllers;

use App\Models\CompanyPolicy;
use Illuminate\Http\Request;
use App\Models\Utility;
use File;

use Illuminate\Support\Facades\Auth;

class CompanyPolicyController extends Controller
{
    public function index()
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee' || \Auth::user()->type == 'client')
        {
            $companyPolicy = CompanyPolicy::where('created_by', '=', \Auth::user()->creatorId())->get();

            return view('companyPolicy.index', compact('companyPolicy'));
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

            return view('companyPolicy.create');
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
                                   'title' => 'required',

                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            if(!empty($request->attachment))
            {
                $filenameWithExt = $request->file('attachment')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('attachment')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . date('m') . '.' . $extension;
                $settings = Utility::getStorageSetting();
                // $dir             = storage_path('uploads/companyPolicy/');
                // if(!file_exists($dir))
                // {
                //     mkdir($dir, 0777, true);
                // }
                // $path = $request->file('attachment')->storeAs('uploads/companyPolicy/', $fileNameToStore);
                if($settings['storage_setting']=='local'){
                    $dir        = 'uploads/companyPolicy/';

                }
                else{
                        $dir        = 'uploads/companyPolicy';
                    }

                $url = '';
                $path = Utility::upload_file($request,'attachment',$fileNameToStore,$dir,[]);

                if($path['flag'] == 1){
                    $url = $path['url'];
                }else{
                    return redirect()->route('company-policy.index', \Auth::user()->id)->with('error', __($path['msg']));
                }
            }

            $policy              = new CompanyPolicy();
            $policy->title       = $request->title;
            $policy->description = !empty($request->description) ? $request->description :'';
            $policy->attachment  = !empty($request->attachment) ? $fileNameToStore : '';
            $policy->created_by  = \Auth::user()->creatorId();
            $policy->save();
            $settings  = Utility::settings();

            // if(isset($settings['company_policy_create_notification']) && $settings['company_policy_create_notification'] ==1){
            //     $msg = $request->title." ".__("created").'.';
            //     Utility::send_slack_msg($msg);
            // }

            if (isset($settings['company_policy_create_notification']) && $settings['company_policy_create_notification'] == 1) {
                $uArr = [
                    'user_name' => \Auth::user()->name,
                    'company_policy_name' => $policy->title
                    ];
                //  dd($uArr);
                Utility::send_slack_msg('new_company_policy', $uArr);
                }
                if (isset($settings['telegram_company_policy_create_notification']) && $settings['telegram_company_policy_create_notification'] == 1) {
                    $uArr = [
                        'user_name' => \Auth::user()->name,
                        'company_policy_name' => $policy->title
                        ];
                    //  dd($uArr);
                    Utility::send_telegram_msg('new_company_policy', $uArr);
                    }

         //webhook
        $module = "New Company policy";
        $webhook = Utility::webhookSetting($module);
        if($webhook)
        {
            $parameter = json_encode($policy);

            // 1 parameter is URL , 2  (policy Data) parameter is data , 3 parameter is method
            $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
            if($status == true)
            {
                return redirect()->back()->with('success', __('Company policy successfully created.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Company policy call failed.'));
            }
        }
            return redirect()->route('company-policy.index')->with('success', __('Company policy successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show(CompanyPolicy $companyPolicy)
    {
        //
    }


    public function edit(CompanyPolicy $companyPolicy)
    {

        if(\Auth::user()->type == 'company')
        {
            return view('companyPolicy.edit', compact('companyPolicy'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function update(Request $request, CompanyPolicy $companyPolicy)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [

                                   'title' => 'required',
                                   'attachment' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            if(isset($request->attachment))
            {
                $filenameWithExt = $request->file('attachment')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('attachment')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . date('m') . '.' . $extension;
                // $dir             = storage_path('uploads/companyPolicy/');
                // if(!file_exists($dir))
                // {
                //     mkdir($dir, 0777, true);
                // }
                // $path = $request->file('attachment')->storeAs('uploads/companyPolicy/', $fileNameToStore);
                $settings = Utility::getStorageSetting();
                if($companyPolicy->attachment)
                {
                    \File::delete(storage_path('uploads/companyPolicy/' . $companyPolicy->attachment));
                }
                if($settings['storage_setting']=='local'){
                    $dir        = 'uploads/companyPolicy/';

                }
                else{
                        $dir        = 'uploads/companyPolicy';
                    }

                $url = '';
                $path = Utility::upload_file($request,'attachment',$fileNameToStore,$dir,[]);

                if($path['flag'] == 1){
                    $url = $path['url'];
                }else{
                    return redirect()->route('company-policy.index', \Auth::user()->id)->with('error', __($path['msg']));
                }
            }

            $companyPolicy->title       = $request->title;
            $companyPolicy->description = $request->description;
            if(isset($request->attachment))
            {
                $companyPolicy->attachment = $fileNameToStore;
            }
            $companyPolicy->created_by = \Auth::user()->creatorId();
            $companyPolicy->save();

            return redirect()->route('company-policy.index')->with('success', __('Company policy successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(CompanyPolicy $companyPolicy)
    {

        if(\Auth::user()->type == 'company')
        {
            $companyPolicy->delete();

            $dir = storage_path('uploads/companyPolicy/');
            if(!empty($companyPolicy->attachment))
            {
                unlink($dir . $companyPolicy->attachment);
            }

            return redirect()->route('company-policy.index')->with('success', __('Company policy successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}

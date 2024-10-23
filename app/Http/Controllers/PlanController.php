<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Utility;
use Illuminate\Http\Request;
use Auth;
use  App\Models\User;
class PlanController extends Controller
{
    public function index()
    {
        if (\Auth::user()->type == 'super admin' || (\Auth::user()->type == 'company')) {
            if(\Auth::user()->type == 'super admin'){
                $plans = Plan::get();
            }
           else{
            $plans = Plan::where('is_active',1)->get();
           }
            $admin_payment_setting = Utility::getAdminPaymentSetting();
            return view('plan.index', compact('plans', 'admin_payment_setting'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->type == 'super admin') {
            $arrDuration = Plan::$arrDuration;
            return view('plan.create', compact('arrDuration'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->type == 'super admin') {
            $admin_payment_setting = Utility::getAdminPaymentSetting();
            if (
                !empty($admin_payment_setting) && ($admin_payment_setting['is_manually_enabled'] == 'on' || $admin_payment_setting['is_bank_transfer_enabled'] == 'on' || $admin_payment_setting['is_stripe_enabled'] == 'on' || $admin_payment_setting['is_paypal_enabled'] == 'on' || $admin_payment_setting['is_paystack_enabled'] == 'on' || $admin_payment_setting['is_flutterwave_enabled'] == 'on' || $admin_payment_setting['is_razorpay_enabled'] == 'on' || $admin_payment_setting['is_mercado_enabled'] == 'on' || $admin_payment_setting['is_paytm_enabled'] == 'on' || $admin_payment_setting['is_mollie_enabled'] == 'on' || $admin_payment_setting['is_paypal_enabled'] == 'on' || $admin_payment_setting['is_skrill_enabled'] == 'on' || $admin_payment_setting['is_coingate_enabled'] == 'on' || $admin_payment_setting['is_paymentwall_enabled'] == 'on' || $admin_payment_setting['is_toyyibpay_enabled'] == 'on' || $admin_payment_setting['is_payfast_enabled'] == 'on' || $admin_payment_setting['is_iyzipay_enabled'] == 'on' || $admin_payment_setting['is_sspay_enabled'] == 'on' || $admin_payment_setting['is_paytab_enabled'] == 'on' || $admin_payment_setting['is_benefit_enabled'] == 'on' || $admin_payment_setting['is_cashfree_enabled'] == 'on' || $admin_payment_setting['is_aamarpay_enabled'] == 'on' || $admin_payment_setting['is_paytr_enabled'] == 'on' || $admin_payment_setting['is_PaiementPro_enabled'] == 'on' || $admin_payment_setting['is_CinetPay_enabled'] == 'on'|| $admin_payment_setting['is_Fedapay_enabled'] == 'on'|| $admin_payment_setting['is_PayHere_enabled'] == 'on'
                )
            ) {
                $validation                     = [];
                $validation['name']             = 'required|unique:plans';
                $validation['price']            = 'required|numeric|min:0';
                $validation['max_employee']     = 'required|numeric';
                $validation['max_client']       = 'required|numeric';
                $validation['duration']         = 'required';
                $validation['storage_limit']    = 'required|numeric|max:999999';
                if ($request->image) {
                    $validation['image']        = 'required|max:20480';
                }
                $request->validate($validation);
                $post = $request->all();
                if ($request->hasFile('image')) {
                    $filenameWithExt = $request->file('image')->getClientOriginalName();
                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('image')->getClientOriginalExtension();
                    $fileNameToStore = 'plan_' . time() . '.' . $extension;

                    $dir = storage_path('uploads/plan/');
                    if (!file_exists($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    $path                = $request->file('image')->storeAs('uploads/plan/', $fileNameToStore);
                    $post['image']       = $fileNameToStore;
                }

                if (Plan::create($post)) {
                    return redirect()->back()->with('success', __('Plan Successfully created.'));
                } else {
                    return redirect()->back()->with('error', __('Something is wrong.'));
                }
            } else {
                return redirect()->back()->with('error', __('Please set stripe or paypal api key & secret key for add new plan.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function edit($plan_id)
    {
        if (\Auth::user()->type == 'super admin') {
            $arrDuration    = Plan::$arrDuration;
            $plan           = Plan::find($plan_id);

            return view('plan.edit', compact('plan', 'arrDuration'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        $userPlan = User::where('plan' , $id)->first();
        if($userPlan != null)
        {
            return redirect()->back()->with('error',__('The company has subscribed to this plan, so it cannot be deleted.'));
        }
        if (\Auth::user()->type == 'super admin') {
            $plan = Plan::find($id);
            $plan->delete();
            return redirect()->route('plan.index')->with('success', __('plan successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, $plan_id)
    {
        if (\Auth::user()->type == 'super admin') {
            $admin_payment_setting = Utility::getAdminPaymentSetting();
            if ($admin_payment_setting['is_stripe_enabled'] == 'on' || $admin_payment_setting['is_paypal_enabled'] == 'on' || $admin_payment_setting['is_paystack_enabled'] == 'on' || $admin_payment_setting['is_flutterwave_enabled'] == 'on' || $admin_payment_setting['is_razorpay_enabled'] == 'on' || $admin_payment_setting['is_mercado_enabled'] == 'on' || $admin_payment_setting['is_paytm_enabled'] == 'on' || $admin_payment_setting['is_mollie_enabled'] == 'on' || $admin_payment_setting['is_paypal_enabled'] == 'on' || $admin_payment_setting['is_skrill_enabled'] == 'on' || $admin_payment_setting['is_coingate_enabled'] == 'on') {;
                $plan = Plan::find($plan_id);
                if (!empty($plan)) {
                    $validation                     = [];
                    $validation['name']             = 'required|unique:plans,name,' . $plan_id;

                    $validation['max_employee']     = 'required|numeric';
                    $validation['max_client']       = 'required|numeric';
                    $validation['storage_limit']    = 'required|numeric|max:999999';

                    // $request->validate($validation);
                    if (!$request->validate($validation)) {
                        return redirect()->back()->with('error', __('Something is wrong.'));
                    }
                    $post = $request->all();

                    if ($request->hasFile('image')) {
                        $filenameWithExt     =   $request->file('image')->getClientOriginalName();
                        $filename            =   pathinfo($filenameWithExt, PATHINFO_FILENAME);
                        $extension           =   $request->file('image')->getClientOriginalExtension();
                        $fileNameToStore     =   'plan_' . time() . '.' . $extension;

                        $dir = storage_path('uploads/plan/');
                        if (!file_exists($dir)) {
                            mkdir($dir, 0777, true);
                        }
                        $image_path = $dir . '/' . $plan->image;  // Value is not URL but directory file path
                        if (\File::exists($image_path)) {

                            chmod($image_path, 0755);
                            \File::delete($image_path);
                        }
                        $path = $request->file('image')->storeAs('uploads/plan/', $fileNameToStore);

                        $post['image'] = $fileNameToStore;
                    }

                    if ($plan->update($post)) {
                        return redirect()->back()->with('success', __('Plan successfully updated.'));
                    } else {
                        return redirect()->back()->with('error', __('Something is wrong.'));
                    }
                } else {
                    return redirect()->back()->with('error', __('Plan not found.'));
                }
            } else {
                return redirect()->back()->with('error', __('Please set stripe api key & secret key for add new plan.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function userPlan(Request $request)
    {
        $objUser = \Auth::user();
        $planID  = \Illuminate\Support\Facades\Crypt::decrypt($request->code);
        $plan    = Plan::find($planID);
        if ($plan) {
            if ($plan->price <= 0) {
                $objUser->assignPlan($plan->id);

                return redirect()->route('plan.index')->with('success', __('Plan successfully activated.'));
            } else {
                return redirect()->back()->with('error', __('Something is wrong.'));
            }
        } else {
            return redirect()->back()->with('error', __('Plan not found.'));
        }
    }

    public function planTrial($plan)
    {
        $objUser = \Auth::user();
        $planID  = \Illuminate\Support\Facades\Crypt::decrypt($plan);
        $plan    = Plan::find($planID);

        if($plan)
        {
            if($plan->price > 0)
            {
                $user = User::find($objUser->id);

                $user->trial_plan = $planID;
                $currentDate = date('Y-m-d');
                $numberOfDaysToAdd = $plan->trial_days;

                $newDate = date('Y-m-d', strtotime($currentDate . ' + ' . $numberOfDaysToAdd . ' days'));
                $user->trial_expire_date = $newDate;
                $user->save();

                $objUser->assignPlan($planID);

                return redirect()->route('plan.index')->with('success', __('Plan successfully activated.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Something is wrong.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Plan not found.'));
        }
    }
    public function planActive(Request $request)
    {
        $userPlan = User::where('plan' , $request->id)->first();
        if($userPlan != null)
        {
            return response()->json(['error' =>__('The company has subscribed to this plan, so it cannot be disabled.')]);
        }
        Plan::where('id', $request->id)->update(['is_active' => $request->is_active]);

        if ($request->is_active == 1) {
            return response()->json(['success' => __('Plan successfully enable.')]);
        } else {
            return response()->json(['success' => __('Plan successfully disable.')]);
        }
    }
}

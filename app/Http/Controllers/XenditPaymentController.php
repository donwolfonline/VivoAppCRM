<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\User;
use App\Models\UserCoupon;
use App\Models\Utility;
use Illuminate\Http\Request;
use Xendit\Xendit;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Plan;
use App\Models\Order;
use App\Models\transactions;
use Illuminate\Support\Facades\DB;

class XenditPaymentController extends Controller
{

    public function planPayWithXendit(Request $request)
    {
        $payment_setting = Utility::getAdminPaymentSetting();
        $xendit_api = $payment_setting['xendit_api_key'];
        $currency = Utility::getAdminCurrency();

        $planID = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $plan = Plan::find($planID);
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        $authuser = Auth::user();
        if ($plan) {
            $get_amount = $plan->price;

            if (!empty($request->coupon)) {
                $coupons = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                if (!empty($coupons)) {
                    $usedCoupun = $coupons->used_coupon();
                    $discount_value = ($plan->price / 100) * $coupons->discount;
                    $get_amount = $plan->price - $discount_value;
                    $userCoupon = new UserCoupon();
                    $userCoupon->user = Auth::user()->id;
                    $userCoupon->coupon = $coupons->id;
                    $userCoupon->order = $orderID;
                    $userCoupon->save();
                    if ($coupons->limit == $usedCoupun) {
                        return redirect()->back()->with('error', __('This coupon code has expired.'));
                    }
                } else {
                    return redirect()->back()->with('error', __('This coupon code is invalid or has expired.'));
                }
            }

            if($get_amount <= 0)
            {
                $authuser->plan = $plan->id;
                $authuser->save();

                $assignPlan = $authuser->assignPlan($plan->id,$authuser->id);

                if($assignPlan['is_success'] == true && !empty($plan))
                {
                    if(!empty($authuser->payment_subscription_id) && $authuser->payment_subscription_id != '')
                    {
                        try
                        {
                            $authuser->cancel_subscription($authuser->id);
                        }
                        catch(\Exception $exception)
                        {
                            \Log::debug($exception->getMessage());
                        }
                    }

                    Order::create(
                        [
                            'order_id' => $orderID,
                            'name' => null,
                            'email' => null,
                            'card_number' => null,
                            'card_exp_month' => null,
                            'card_exp_year' => null,
                            'plan_name' => $plan->name,
                            'plan_id' => $plan->id,
                            'price' => $get_amount == null ? 0 : $get_amount,
                            'price_currency' => $currency,
                            'txn_id' => '',
                            'payment_type' => __('Zero Price'),
                            'payment_status' => 'succeeded',
                            'receipt' => null,
                            'user_id' => $authuser->id,
                        ]
                    );

                    return redirect()->route('plan.index')->with('success', __('Plan activated Successfully!'));
                }
                else
                {
                    return redirect()->back()->with('error', __('Plan fail to upgrade.'));
                }
            }

            $response = ['orderId' => $orderID, 'user' => $authuser, 'get_amount' => $get_amount, 'plan' => $plan, 'currency' => $currency];
            Xendit::setApiKey($xendit_api);
            $params = [
                'external_id' => $orderID,
                'payer_email' => Auth::user()->email,
                'description' => 'Payment for order ' . $orderID,
                'amount' => $get_amount,
                'callback_url' =>  route('plan.xendit.status'),
                'success_redirect_url' => route('plan.xendit.status', $response),
                'failure_redirect_url' => route('plan.index'),
            ];

            $invoice = \Xendit\Invoice::create($params);
            Session::put('invoice',$invoice);

            return redirect($invoice['invoice_url']);
        }
    }

    public function planGetXenditStatus(Request $request)
    {
        $payment_setting = Utility::getAdminPaymentSetting();
        $xendit_api = $payment_setting['xendit_api_key'];
        Xendit::setApiKey($xendit_api);

        $session = Session::get('invoice');
        $getInvoice = \Xendit\Invoice::retrieve($session['id']);

        $authuser = User::find($request->user);
        $plan = Plan::find($request->plan);

        if($getInvoice['status'] == 'PAID'){

            Order::create(
                [
                    'order_id' => $request->orderId,
                    'name' => null,
                    'email' => null,
                    'card_number' => null,
                    'card_exp_month' => null,
                    'card_exp_year' => null,
                    'plan_name' => $plan->name,
                    'plan_id' => $plan->id,
                    'price' => $request->get_amount == null ? 0 : $request->get_amount,
                    'price_currency' => $request->currency,
                    'txn_id' => '',
                    'payment_type' => __('Xendit'),
                    'payment_status' => 'succeeded',
                    'receipt' => null,
                    'user_id' => $request->user,
                ]
            );
            $user    = \Auth::user();
            $referral = DB::table('referral_programs')->first();
           $amount=  ($plan->price * $referral->commission) /100;
           $referral = DB::table('referral_programs')->first();
           $transactions = transactions::where('uid', $user->id)->get();
           $total=count($transactions);
           if (isset($referral) && $referral->Reffral_enabled == "on") {

          if($user->used_referral_code !== null && $total == 0)
           {


               transactions::create(
                   [
                       'referral_code' => $user->referral_code,
                       'used_referral_code' => $user->used_referral_code,
                       'company_name' => $user->name,
                       'plane_name' => $plan->name,
                       'plan_price'=> $plan->price,
                       'commission'=>$referral->commission,
                       'commission_amount'=>$amount,
                       'uid' => $user->id,
                   ]
               );
           }
        }

            $assignPlan = $authuser->assignPlan($plan->id, $request->payment_frequency);

            if($assignPlan['is_success'])
            {
                return redirect()->route('plan.index')->with('success', __('Plan activated Successfully!'));
            }
            else
            {
                return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
            }
        }
    }

    public function invoicePayWithXendit(Request $request)
    {
        $invoice_id = $request->invoice_id;
        $invoice = Invoice::find($invoice_id);
        if(Auth::check()){
            $user = Auth::user();
        }
        else{
            $user=User::where('id',$invoice->created_by)->first();
        }

        $get_amount = $request->amount;
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

        try {
            if ($invoice) {
                $payment_setting = Utility::getCompanyPaymentSetting($user->id);
                $xendit_api = $payment_setting['xendit_api_key'];
                $currency_code = isset($payment_setting['site_currency']) ? $payment_setting['site_currency'] : 'RUB';
                $response = ['orderId' => $orderID, 'user' => $user, 'get_amount' => $get_amount, 'invoice' => $invoice, 'currency' => $currency_code];
                Xendit::setApiKey($xendit_api);
                $params = [
                    'external_id' => $orderID,
                    'payer_email' => $user->email,
                    'description' => 'Payment for order ' . $orderID,
                    'amount' => $get_amount,
                    'callback_url' =>  route('invoice.xendit.status'),
                    'success_redirect_url' => route('invoice.xendit.status', $response),
                ];

                $Xenditinvoice = \Xendit\Invoice::create($params);
                Session::put('invoicepay',$Xenditinvoice);
                return redirect($Xenditinvoice['invoice_url']);

            } else {
                return redirect()->back()->with('error', 'Invoice not found.');
            }
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', __($e));
        }
    }

    public function getInvociePaymentStatus(Request $request)
    {
        $data = request()->all();
        $fixedData = [];
        foreach ($data as $key => $value) {
            $fixedKey = str_replace('amp;', '', $key);
            $fixedData[$fixedKey] = $value;
        }
        $settings  = Utility::settings();

        $session = Session::get('invoicepay');
        $invoice = Invoice::find($fixedData['invoice']);
        $invoice_id = $invoice->id;

        if(Auth::check()){
            $user = Auth::user();
        }
        else{
            $user=User::where('id',$invoice->created_by)->first();
        }

        if ($invoice) {
            try {
                $payment_setting = Utility::getCompanyPaymentSetting($user->id);
                $orderID  = strtoupper(str_replace('.', '', uniqid('', true)));
                $xendit_api = $payment_setting['xendit_api_key'];
                Xendit::setApiKey($xendit_api);
                $getInvoice = \Xendit\Invoice::retrieve($session['id']);

                if($getInvoice['status'] == 'PAID'){

                    try {
                        $payments = New InvoicePayment;
                        $payments->transaction = $orderID;
                        $payments->invoice = $invoice->id;
                        $payments->amount = $fixedData['get_amount'] == null ? 0 : $fixedData['get_amount'];
                        $payments->date = date('Y-m-d');
                        $payments->payment_method = 1;
                        $payments->payment_type = __('Xendit');
                        $payments->notes = __('Invoice') . ' ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id);
                        $payments->receipt = '';
                        $payments->created_by = $user->creatorId();
                        $payments->save();

                        if($invoice->getDue() <= 0.0)
                        {
                            Invoice::change_status($invoice->id, 5);
                        }
                        elseif($invoice->getDue() > 0)
                        {
                            Invoice::change_status($invoice->id, 4);
                        }
                        else
                        {
                            Invoice::change_status($invoice->id, 3);
                        }

                        $invoice->save();

                        if (isset($settings['payment_create_notification']) && $settings['payment_create_notification'] == 1) {
                            $uArr = [
                                'user_name' => $user->name,
                                'amount'=> $amount,
                                'created_by'=> 'by Paytm',
                            ];
                            Utility::send_slack_msg('new_payment', $uArr);
                            }
                        if (isset($settings['telegram_payment_create_notification']) && $settings['telegram_payment_create_notification'] == 1) {
                            $uArr = [
                                'user_name' => $user->name,
                                'amount'=> $amount,
                                'created_by'=> 'by Paytm',
                            ];
                            Utility::send_telegram_msg('new_payment', $uArr);
                            }
                        if (isset($settings['twilio_invoice_payment_create_notification']) && $settings['twilio_invoice_payment_create_notification'] == 1)
                        {
                            $uArr = [
                                'user_name' => $user->name,
                                'amount'=> $amount,
                                'created_by'=> 'by Paytm',
                            ];
                            Utility::send_twilio_msg('new_payment', $uArr);
                        }

                        $module ='Invoice Status Update';
                        $webhook=  Utility::webhookSetting($module,$invoice->created_by);
                        if($webhook)
                        {
                            $parameter = json_encode($invoice);
                            // 1 parameter is  URL , 2 parameter is data , 3 parameter is method
                            $status = Utility::WebhookCall($webhook['url'],$parameter,$webhook['method']);
                            // dd($status);
                            // if($status == true)
                            // {
                            //     return redirect()->back()->with('success', __('Payment added Successfully!'));
                            // }
                            // else
                            // {
                            //     return redirect()->back()->with('error', __('Webhook call failed.'));
                            // }
                        }

                        if (Auth::user())
                        {
                            return redirect()->route('invoice.show', \Crypt::encrypt($invoice_id))->with('success', __('Invoice paid Successfully!'));
                        } else {
                            return redirect()->route('pay.invoice', \Crypt::encrypt($invoice_id))->with('success', __('Invoice paid Successfully!'));
                        }
                    } catch (\Exception $e) {
                        if (Auth::check()) {
                            return redirect()->route('invoice.show', \Crypt::encrypt($invoice_id))->with('error', $e->getMessage());
                        } else {
                            return redirect()->route('pay.invoice', \Crypt::encrypt($invoice_id))->with('error', $e->getMessage());
                        }
                    }
                }
            } catch (\Exception $e) {
                if (Auth::check()) {
                    return redirect()->route('invoice.show', \Crypt::encrypt($invoice_id))->with('error', $e->getMessage());
                } else {
                    return redirect()->route('pay.invoice', \Crypt::encrypt($invoice_id))->with('error', $e->getMessage());
                }
            }
        }else{
            if (Auth::check()) {
                return redirect()->route('invoice.show', \Crypt::encrypt($invoice_id))->with('error', __('Invoice not found.'));
            } else {
                return redirect()->route('pay.invoice', \Crypt::encrypt($invoice_id))->with('error', __('Invoice not found.'));
            }
        }
    }
}

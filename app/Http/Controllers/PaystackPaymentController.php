<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserCoupon;
use App\Models\Utility;
use App\Models\Client;
use App\Models\transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaystackPaymentController extends Controller
{
    //
    public $secret_key;
    public $public_key;
    public $is_enabled;


    public function paymentConfig($user)
    {
        if(Auth::check())
        {

            $payment_setting = Utility::getAdminPaymentSetting();

        }
        else
        {
            $payment_setting = Utility::getCompanyPaymentSettingWithOutAuth($user->id);

        }

        $this->secret_key = isset($payment_setting['paystack_secret_key']) ? $payment_setting['paystack_secret_key'] : '';
        $this->public_key = isset($payment_setting['paystack_public_key']) ? $payment_setting['paystack_public_key'] : '';
        $this->is_enabled = isset($payment_setting['is_paystack_enabled']) ? $payment_setting['is_paystack_enabled'] : 'off';
        return $this;
    }

    public function planPayWithPaystack(Request $request)
    {
        $planID    = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $plan      = Plan::find($planID);
        $authuser  = \Auth::user();
        $coupon_id = '';
        if($plan)
        {
            $price = $plan->price;
            if(isset($request->coupon) && !empty($request->coupon))
            {
                $coupons = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                if(!empty($coupons))
                {
                    $usedCoupun     = $coupons->used_coupon();
                    $discount_value = ($plan->price / 100) * $coupons->discount;
                    $price          = $plan->price - $discount_value;
                    if($coupons->limit == $usedCoupun)
                    {
                        return redirect()->back()->with('error', __('This coupon code has expired.'));
                    }
                    $coupon_id = $coupons->id;
                }
                else
                {
                    return redirect()->back()->with('error', __('This coupon code is invalid or has expired.'));
                }
            }

            if($price <= 0)
            {
                $authuser->plan = $plan->id;
                $authuser->save();

                $assignPlan = $authuser->assignPlan($plan->id);

                if($assignPlan['is_success'] == true && !empty($plan))
                {


                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
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
                            'price' => $price,
                            'price_currency' => Utility::getAdminCurrency(),
                            'txn_id' => '',
                            'payment_type' => 'Paystack',
                            'payment_status' => 'succeeded',
                            'receipt' => null,
                            'user_id' => $authuser->id,
                        ]
                    );
                    $res['msg']  = __("Plan successfully upgraded.");
                    $res['flag'] = 2;

                    return $res;
                }
                else
                {
                    return redirect()->route('plan.index')->with('error', __('Plan fail to upgrade.'));

                }
            }
            $res_data['email']       = \Auth::user()->email;
            $res_data['total_price'] = $price;
            $res_data['currency']    = Utility::getAdminCurrency();
            $res_data['flag']        = 1;
            $res_data['coupon']      = $coupon_id;

            return $res_data;
        }
        else
        {
            return redirect()->route('plan.index')->with('error', __('Plan is deleted.'));

        }

    }

    public function getPaymentStatus(Request $request, $pay_id)
    {
        $user    = Auth::user();
        $payment = $this->paymentConfig($user);
        $planID    = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $plan      = Plan::find($planID);

        $result  = array();


        if($plan)
        {
            try
            {
                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                //The parameter after verify/ is the transaction reference to be verified
                $url = "https://api.paystack.co/transaction/verify/$pay_id";
                $ch  = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt(
                    $ch, CURLOPT_HTTPHEADER, [
                           'Authorization: Bearer ' . $this->secret_key,
                       ]
                );
                $responce = curl_exec($ch);
                curl_close($ch);
                if($responce)
                {
                    $result = json_decode($responce, true);
                }

                if(isset($result['status']) && $result['status'] == true)
                {
                    $status = $result['data']['status'];
                    if($request->has('coupon_id') && $request->coupon_id != '')
                    {
                        $coupons = Coupon::find($request->coupon_id);
                        if(!empty($coupons))
                        {
                            $userCoupon         = new UserCoupon();
                            $userCoupon->user   = $user->id;
                            $userCoupon->coupon = $coupons->id;
                            $userCoupon->order  = $orderID;
                            $userCoupon->save();
                            $usedCoupun = $coupons->used_coupon();
                            if($coupons->limit <= $usedCoupun)
                            {
                                $coupons->is_active = 0;
                                $coupons->save();
                            }
                        }
                    }

                    $order                 = new Order();
                    $order->order_id       = $orderID;
                    $order->name           = $user->name;
                    $order->card_number    = '';
                    $order->card_exp_month = '';
                    $order->card_exp_year  = '';
                    $order->plan_name      = $plan->name;
                    $order->plan_id        = $plan->id;
                    $order->price          = $result['data']['amount'] / 100;
                    $order->price_currency = Utility::getAdminCurrency();
                    $order->txn_id         = $pay_id;
                    $order->payment_type   = __('Paystack');
                    $order->payment_status = $result['data']['status'];
                    $order->receipt        = '';
                    $order->user_id        = $user->id;
                    $order->save();


                    $referral = DB::table('referral_programs')->first();
                    $amount=  ($plan->price * $referral->commission) /100;

                    $transactions = transactions::where('uid', $user->id)->get();
                    $total=count($transactions);


                if (isset($referral) && $referral->Reffral_enabled == "on") {


                    if ($user->used_referral_code !== null && $total == 0) {


                        transactions::create(
                            [
                                'referral_code' => $user->referral_code,
                                'used_referral_code' => $user->used_referral_code,
                                'company_name' => $user->name,
                                'plane_name' => $plan->name,
                                'plan_price' => $plan->price,
                                'commission' => $referral->commission,
                                'commission_amount' => $amount,
                                'uid' => $user->id,

                            ]
                        );
                    }
                }

                    $assignPlan = $user->assignPlan($plan->id);
                    if($assignPlan['is_success'])
                    {
                        return redirect()->route('plan.index')->with('success', __('Plan activated Successfully.'));
                    }
                    else
                    {
                        return redirect()->route('plan.index')->with('error', $assignPlan['error']);
                    }
                }
                else
                {
                    return redirect()->back()->with('error', __('Transaction Unsuccesfull'));
                }
            }
            catch(\Exception $e)
            {
                return redirect()->route('plan.index')->with('error', __('Transaction has been failed.'));
            }
        }
        else
        {
            return redirect()->route('plan.index')->with('error', __('Plan is deleted.'));
        }
    }


    public function invoicePayWithPaystack(Request $request)
    {

        $invoiceID = $request->invoice_id;
        $invoiceID = \Crypt::decrypt($invoiceID);

        $invoice   = Invoice::find($invoiceID);


        if(Auth::check()){
            $user = \Auth::user();
        }else{
            $user= User::where('id',$invoice->created_by)->first();
        }

        if (Auth::check()) {
             $payment_setting = Utility::getNonAuthCompanyPaymentSetting($invoice->created_by);
            $this->secret_key = isset($payment_setting['paystack_secret_key']) ? $payment_setting['paystack_secret_key'] : '';
            $this->public_key = isset($payment_setting['paystack_public_key']) ? $payment_setting['paystack_public_key'] : '';
            $this->is_enabled = isset($payment_setting['is_paystack_enabled']) ? $payment_setting['is_paystack_enabled'] : 'off';
             $settings = Utility::settingsById($invoice->created_by);

        } else {

            //$payment = $this->paymentConfig($user);
           // $settings = DB::table('settings')->where('created_by', '=',\Auth::user()->creatorId())->get()->pluck('value', 'name');
            $settings  = DB::table('settings')->where('created_by', '=', $user->creatorId())->get()->pluck('value', 'name');
            //dd($settings);
        }

        if($invoice)
        {
            $price = $request->amount;
            if($price > 0)
            {
                $res_data['email']       = $user->email;
                $res_data['total_price'] = $price;
                $res_data['currency']    = Utility::settingsById($invoice->created_by)['site_currency'];
                // dd($res_data['currency']);
                // $res_data['currency'] = Utility::getAdminCurrency();
                $res_data['flag']        = 1;
                $request->session()->put('invoice_data', $res_data);
                return $res_data;

            }
            else
            {
                $res['msg']  = __("Enter valid amount.");
                $res['flag'] = 2;

                return $res;
            }

        }
        else
        {
            return redirect()->route('invoice.index')->with('error', __('Invoice is deleted.'));

        }

    }

    public function getInvoicePaymentStatus(Request $request, $pay_id, $invoice_id)
    {

        $invoiceID = \Illuminate\Support\Facades\Crypt::decrypt($invoice_id);
        $invoice   = Invoice::find($invoiceID);
        $invoice_data =  $request->session()->get('invoice_data') ;
        if(\Auth::check())
        {
            $user=\Auth::user();
        }
        else
        {
            $user= User::where('id',$invoice->created_by)->first();
        }
        // dd($payment);
        $orderID  = strtoupper(str_replace('.', '', uniqid('', true)));
        $settings = DB::table('settings')->where('created_by', '=', $user->creatorId())->get()->pluck('value', 'name');

        if (Auth::check()) {
            $payment_setting = Utility::getNonAuthCompanyPaymentSetting($invoice->created_by);
            $this->secret_key = isset($payment_setting['paystack_secret_key']) ? $payment_setting['paystack_secret_key'] : '';
            $this->public_key = isset($payment_setting['paystack_public_key']) ? $payment_setting['paystack_public_key'] : '';
            $this->is_enabled = isset($payment_setting['is_paystack_enabled']) ? $payment_setting['is_paystack_enabled'] : 'off';
             $settings = Utility::settingsById($invoice->created_by);

        } else {
            $payment = $this->paymentConfig($user);
            $settings = DB::table('settings')->where('created_by', '=', $user->creatorId())->get()->pluck('value', 'name');
        }

        $result    = array();

        if($invoice)
        {
            try
            {

                //The parameter after verify/ is the transaction reference to be verified
                $url = "https://api.paystack.co/transaction/verify/$pay_id";
                $ch  = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt(
                    $ch, CURLOPT_HTTPHEADER, [
                           'Authorization: Bearer ' . $this->secret_key,
                       ]
                );
                $responce = curl_exec($ch);
                curl_close($ch);
              //  dd($responce);

                if($responce)
                {
                    $result = json_decode($responce, true);
                }

                //dd($result);

                if(isset($result['status']) && $result['status'] == true)
                    {
                    $payments = New InvoicePayment;
                    $payments->transaction = $orderID;
                    $payments->invoice = $invoice->id;
                    $payments->amount =isset($invoice_data['total_price']) ? $invoice_data['total_price'] : 0;
                    //    dd($payments->amount);
                    $payments->date = date('Y-m-d');
                    $payments->payment_method = 1;
                    $payments->payment_type = __('Paystack');
                    $payments->notes = __('Invoice') . ' ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id);
                    $payments->receipt = '';
                    $payments->created_by = $user->creatorId();
                    $payments->save();

                    $invoice = Invoice::find($invoice->id);

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


                    if(\Auth::check())
                    {
                         $user = Auth::user();
                    }
                    else
                    {
                       $user=User::where('id',$invoice->created_by)->first();
                    }

                    $settings  = Utility::settings();
                    // if(isset($settings['payment_create_notification']) && $settings['payment_create_notification'] ==1){

                    //     $msg = __('New payment of ').$request->amount.' '.__('created for ').$user->name.__(' by Paystack').'.';
                    //     //dd($msg);
                    //     Utility::send_slack_msg($msg);
                    // }

                    if (isset($settings['payment_create_notification']) && $settings['payment_create_notification'] == 1) {
                        $uArr = [
                            'user_name' => $user->name,
                            'amount'=> $request->amount,
                            'created_by'=> 'by Paystack',
                        ];
                        Utility::send_slack_msg('new_payment', $uArr);
                        }

                    if (isset($settings['telegram_payment_create_notification']) && $settings['telegram_payment_create_notification'] == 1) {
                        $uArr = [
                            'user_name' => $user->name,
                            'amount'=> $request->amount,
                            'created_by'=> 'by Paystack',
                        ];
                        Utility::send_telegram_msg('new_payment', $uArr);
                        }
                    if (isset($settings['twilio_invoice_payment_create_notification']) && $settings['twilio_invoice_payment_create_notification'] == 1) {
                        $uArr = [
                            'user_name' => $user->name,
                            'amount'=> $request->amount,
                            'created_by'=> 'by Paystack',
                        ];
                        Utility::send_twilio_msg('new_payment', $uArr);
                        }

                        $module ='Invoice Status Update';
                        $webhook=  Utility::webhookSetting($module);
                        if($webhook)
                        {
                            $parameter = json_encode($invoice);
                            // 1 parameter is  URL , 2 parameter is data , 3 parameter is method
                            $status = Utility::WebhookCall($webhook['url'],$parameter,$webhook['method']);
                            // dd($status);
                            if($status == true)
                            {
                                return redirect()->back()->with('success', __('Payment added Successfully!'));
                            }
                            else
                            {
                                return redirect()->back()->with('error', __('Webhook call failed.'));
                            }
                        }
                    return redirect()->back()->with('success', __(' Payment successfully added.'));

                }
                else
                {
                    return redirect()->back()->with('error', __('Transaction Unsuccesfull'));
                }
            }
            catch(\Exception $e)
            {
                return redirect()->back()->with('error', __('Transaction has been failed.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Invoice is deleted.'));
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Order;
use App\Models\UserCoupon;
use App\Models\Utility;
use Illuminate\Http\Request;
use Twilio\TwiML\Voice\Stop;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\InvoicePayment;
use App\Models\transactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaytrController extends Controller
{
    public function PlanpayWithPaytr(Request $request)
    {
        $payment_setting = Utility::getAdminPaymentSetting();
        $paytr_merchant_id = $payment_setting['paytr_merchant_id'];
        $paytr_merchant_key = $payment_setting['paytr_merchant_key'];
        $paytr_merchant_salt = $payment_setting['paytr_merchant_salt'];
        $currency = 'TL';
        $planID = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $authuser = \Auth::user();
        $plan = Plan::find($planID);
        if ($plan) {

            $get_amount = $plan->price;

            if (!empty($request->coupon)) {
                $coupons = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                if (!empty($coupons)) {
                    $usedCoupun = $coupons->used_coupon();
                    $discount_value = ($plan->price / 100) * $coupons->discount;

                    $get_amount = $plan->price - $discount_value;

                    if ($coupons->limit == $usedCoupun) {
                        return redirect()->back()->with('error', __('This coupon code has expired.'));
                    }
                    if ($get_amount <= 0) {
                        $authuser = Auth::user();
                        $authuser->plan = $plan->id;
                        $authuser->save();
                        $assignPlan = $authuser->assignPlan($plan->id);
                        if ($assignPlan['is_success'] == true && !empty($plan)) {
                            if (!empty($authuser->payment_subscription_id) && $authuser->payment_subscription_id != '') {
                                try {
                                    $authuser->cancel_subscription($authuser->id);
                                } catch (\Exception $exception) {
                                    \Log::debug($exception->getMessage());
                                }
                            }
                            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                            $userCoupon = new UserCoupon();
                            // dd($coupons->id);
                            $userCoupon->user = $authuser->id;
                            $userCoupon->coupon = $coupons->id;
                            $userCoupon->order = $orderID;
                            $userCoupon->save();
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
                                    'price_currency' => Utility::getAdminCurrency(),
                                    'txn_id' => '',
                                    'payment_type' => 'PayTr',
                                    'payment_status' => 'success',
                                    'receipt' => null,
                                    'user_id' => $authuser->id,
                                ]
                            );
                            $assignPlan = $authuser->assignPlan($plan->id);
                            return redirect()->route('plan.index')->with('success', __('Plan Successfully Activated'));
                        }
                    }
                } else {
                    return redirect()->back()->with('error', __('This coupon code is invalid or has expired.'));
                }
            }
            try {
                $coupon = (empty($request->coupon)) ? "0" : $request->coupon;

                $merchant_id    = $paytr_merchant_id;
                $merchant_key   = $paytr_merchant_key;
                $merchant_salt  = $paytr_merchant_salt;

                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                $email = $authuser->email;
                $payment_amount = $get_amount;
                $merchant_oid = $orderID;
                $user_name = $authuser->name;
                $user_address = 'no address';
                $user_phone = '0000000000';


                $user_basket = base64_encode(json_encode(array(
                    array("Plan", $payment_amount, 1),
                )));

                if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                    $ip = $_SERVER["HTTP_CLIENT_IP"];
                } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                    $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
                } else {
                    $ip = $_SERVER["REMOTE_ADDR"];
                }

                $user_ip = $ip;
                $timeout_limit = "30";
                $debug_on = 1;
                $test_mode = 0;
                $no_installment = 0;
                $max_installment = 0;
                $currency = "TL";
                $hash_str = $merchant_id . $user_ip . $merchant_oid . $email . $payment_amount . $user_basket . $no_installment . $max_installment . $currency . $test_mode;
                $paytr_token = base64_encode(hash_hmac('sha256', $hash_str . $merchant_salt, $merchant_key, true));

                $request['orderID'] = $orderID;
                $request['plan_id'] = $plan->id;
                $request['price'] = $get_amount;
                $request['payment_status'] = 'failed';
                $payment_failed = $request->all();
                $request['payment_status'] = 'success';
                $payment_success = $request->all();

                $post_vals = array(
                    'merchant_id' => $merchant_id,
                    'user_ip' => $user_ip,
                    'merchant_oid' => $merchant_oid,
                    'email' => $email,
                    'payment_amount' => $payment_amount,
                    'paytr_token' => $paytr_token,
                    'user_basket' => $user_basket,
                    'debug_on' => $debug_on,
                    'no_installment' => $no_installment,
                    'max_installment' => $max_installment,
                    'user_name' => $user_name,
                    'user_address' => $user_address,
                    'user_phone' => $user_phone,
                    'merchant_ok_url' => route('plan.pay.paytr.success', $payment_success),
                    'merchant_fail_url' => route('plan.pay.paytr.success', $payment_failed),
                    'timeout_limit' => $timeout_limit,
                    'currency' => $currency,
                    'test_mode' => $test_mode
                );

                // dd($post_vals);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
                curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 20);


                $result = @curl_exec($ch);

                if (curl_errno($ch)) {
                    die("PAYTR IFRAME connection error. err:" . curl_error($ch));
                }

                curl_close($ch);

                $result = json_decode($result, 1);

                if ($result['status'] == 'success') {
                    $token = $result['token'];
                } else {
                    return redirect()->route('plan.index')->with('error', $result['reason']);
                }

                return view('plan.paytr_payment', compact('token'));
            } catch (\Throwable $th) {
                return redirect()->route('plan.index')->with('error', $th->getMessage());
            }
        }
    }

    public function paytrsuccess(Request $request)
    {
        if ($request->payment_status == "success") {
            try {
                $user = \Auth::user();
                $planID = $request->plan_id;
                $plan = Plan::find($planID);
                $couponCode = $request->coupon;
                $getAmount = $request->price;

                if ($couponCode != 0) {
                    $coupons = Coupon::where('code', strtoupper($couponCode))->where('is_active', '1')->first();
                    $request['coupon_id'] = $coupons->id;
                } else {
                    $coupons = null;
                }

                $order = new Order();
                $order->order_id = $request->orderID;
                $order->name = $user->name;
                $order->card_number = '';
                $order->card_exp_month = '';
                $order->card_exp_year = '';
                $order->plan_name = $plan->name;
                $order->plan_id = $plan->id;
                $order->price = $getAmount;
                $order->price_currency = Utility::getAdminCurrency();
                $order->txn_id = $request->orderID;
                $order->payment_type = __('PayTR');
                $order->payment_status = 'success';
                $order->txn_id = '';
                $order->receipt = '';
                $order->user_id = $user->id;
                $order->save();


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
                $assignPlan = $user->assignPlan($plan->id);

                $coupons = Coupon::find($request->coupon_id);
                if (!empty($request->coupon_id)) {
                    if (!empty($coupons)) {
                        $userCoupon = new UserCoupon();
                        $userCoupon->user = $user->id;
                        $userCoupon->coupon = $coupons->id;
                        $userCoupon->order = $request->orderID;
                        $userCoupon->save();
                        $usedCoupun = $coupons->used_coupon();
                        if ($coupons->limit <= $usedCoupun) {
                            $coupons->is_active = 0;
                            $coupons->save();
                        }
                    }
                }


                if ($assignPlan['is_success']) {
                    return redirect()->route('plan.index')->with('success', __('Plan activated Successfully.'));
                } else {
                    return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
                }
            } catch (\Exception $e) {
                return redirect()->route('plan.index')->with('error', __($e->getMessage()));
            }
        }else{
            return redirect()->route('plan.index')->with('success', __('Your Transaction is fail please try again.'));
        }
    }

    public function invoicePayWithpaytr(Request $request)
    {
        $invoice_id = $request->invoice_id;
        $invoice = Invoice::find($invoice_id);
        $user = User::where('id', $invoice->created_by)->first();

        $payment_setting = Utility::getCompanyPaymentSetting($invoice->created_by);
        $paytr_merchant_id = $payment_setting['paytr_merchant_id'];
        $paytr_merchant_key = $payment_setting['paytr_merchant_key'];
        $paytr_merchant_salt = $payment_setting['paytr_merchant_salt'];

        if(\Auth::check())
        {
            $authuser = $user = \Auth::user();
        }
        else
        {
            $authuser = $user = User::where('id', $invoice->created_by)->first();
        }

        $get_amount = $request->amount;
        if ($invoice && $get_amount != 0)
        {
            if ($get_amount > $invoice->getDue())
            {
                return redirect()->back()->with('error', __('Invalid amount.'));
            }
            else{

                try{

                    $merchant_id    = $paytr_merchant_id;
                    $merchant_key   = $paytr_merchant_key;
                    $merchant_salt  = $paytr_merchant_salt;

                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                    $email = $authuser->email;
                    $payment_amount = $get_amount;
                    $merchant_oid = $orderID;
                    $user_name = $authuser->name;
                    $user_address =  isset($authuser->address)? $authuser->address : 'No Address' ;
                    $user_phone =isset($authuser->telephone) ? $authuser->telephone : '0000000000';


                    $user_basket = base64_encode(json_encode(array(
                        array("Invoice", $payment_amount, 1),
                    )));

                    if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                        $ip = $_SERVER["HTTP_CLIENT_IP"];
                    } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
                    } else {
                        $ip = $_SERVER["REMOTE_ADDR"];
                    }

                    $user_ip = $ip;
                    $timeout_limit = "30";
                    $debug_on = 1;
                    $test_mode = 0;
                    $no_installment = 0;
                    $max_installment = 0;
                    $currency = 'TL';
                    $hash_str = $merchant_id . $user_ip . $merchant_oid . $email . $payment_amount . $user_basket . $no_installment . $max_installment . $currency . $test_mode;
                    $paytr_token = base64_encode(hash_hmac('sha256', $hash_str . $merchant_salt, $merchant_key, true));

                    $request['orderID'] = $orderID;
                    $request['invoice_id'] = $invoice_id;
                    $request['price'] = $get_amount;
                    $request['payment_status'] = 'failed';
                    $payment_failed = $request->all();
                    $request['payment_status'] = 'success';
                    $payment_success = $request->all();

                    $post_vals = array(
                        'merchant_id' => $merchant_id,
                        'user_ip' => $user_ip,
                        'merchant_oid' => $merchant_oid,
                        'email' => $email,
                        'payment_amount' => $payment_amount,
                        'paytr_token' => $paytr_token,
                        'user_basket' => $user_basket,
                        'debug_on' => $debug_on,
                        'no_installment' => $no_installment,
                        'max_installment' => $max_installment,
                        'user_name' => $user_name,
                        'user_address' => $user_address,
                        'user_phone' => $user_phone,
                        'merchant_ok_url' => route('invoice.paytr.status', $payment_success ),
                        'merchant_fail_url' => route('invoice.paytr.status', $payment_failed ),
                        'timeout_limit' => $timeout_limit,
                        'currency' => $currency,
                        'test_mode' => $test_mode
                    );

                    // dd($post_vals);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
                    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 20);


                    $result = @curl_exec($ch);
                    if (curl_errno($ch)) {
                        die("PAYTR IFRAME connection error. err:" . curl_error($ch));
                    }


                    $result = json_decode($result, 1);

                    if ($result['status'] == 'success') {
                        $token = $result['token'];
                    } else {

                        if (Auth::user())
                        {
                            return redirect()->back()->with('error', $result['reason']);
                        }
                        else{
                            return redirect()->route('pay.invoice', [\Illuminate\Support\Facades\Crypt::encrypt($invoice_id)])->with('error', $result['reason']);
                        }

                    }

                    return view('plan.paytr_payment', compact('token'));

                } catch (\Throwable $th) {
                    if (Auth::user())
                    {
                        return redirect()->back()->with('error', $th->getMessage());
                    }
                    else{
                        return redirect()->route('pay.invoice', [\Illuminate\Support\Facades\Crypt::encrypt($invoice_id)])->with('error', $th->getMessage());
                    }
                }
            }
        }
    }

    public function getInvociePaymentStatus(Request $request)
    {

        $invoice_id = $request->invoice_id;
        $invoice = Invoice::find($invoice_id);
        $getAmount = $request->price;
        $orderID =  $request->orderID;

        if(\Auth::check())
        {
            $user=\Auth::user();
        }
        else
        {
            $user= User::where('id',$invoice->created_by)->first();
        }

        $settings = DB::table('settings')->where('created_by', '=', $user->creatorId())->get()->pluck('value', 'name');

        $order_id  = strtoupper(str_replace('.', '', uniqid('', true)));
        // dd($request->all(),$order_id);

        if($request->payment_status == 'success'){
                $payments                 = new InvoicePayment();
                $payments->transaction = $orderID;
                $payments->invoice     = $invoice_id;
                $payments->amount         = $getAmount;
                $payments->date           = date('Y-m-d');
                $payments->payment_method = 1;
                $payments->payment_type = __('PayTr');
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

                //For Notification
                $customer = User::find($invoice->customer_id);
                $setting  = Utility::settingsById($invoice->created_by);
                $notificationArr = [
                    'payment_price' => $getAmount,
                    'customer_name' => $user->name,
                ];
                //Slack Notification
                if(isset($setting['payment_notification']) && $setting['payment_notification'] ==1)
                {
                    Utility::send_slack_msg('new_invoice_payment', $notificationArr,$invoice->created_by);
                }
                //Telegram Notification
                if(isset($setting['telegram_payment_notification']) && $setting['telegram_payment_notification'] == 1)
                {
                    Utility::send_telegram_msg('new_invoice_payment', $notificationArr,$invoice->created_by);
                }

                //Twilio Notification
                if(isset($setting['twilio_payment_notification']) && $setting['twilio_payment_notification'] ==1)
                {
                    Utility::send_twilio_msg($customer->contact,'new_invoice_payment', $notificationArr,$invoice->created_by);
                }

                //webhook
                $module ='New Invoice Payment';
                $webhook=  Utility::webhookSetting($module,$invoice->created_by);
                if($webhook)
                {
                    $parameter = json_encode($invoice);
                    $status = Utility::WebhookCall($webhook['url'],$parameter,$webhook['method']);
                    if($status == true)
                    {
                        return redirect()->back()->with('success', __('Payment successfully added!'));
                    }
                    else
                    {
                        return redirect()->back()->with('error', __('Webhook call failed.'));
                    }
                }

                if (\Auth::check()) {
                     return redirect()->route('invoice.show', \Crypt::encrypt($invoice_id))->with('success', __('Payment successfully added'));
                }
                else
                {
                    return redirect()->route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('success', __('Payment successfully added'));
                }
            } else {
                return redirect()->back()->with('error', __('Your Transaction is fail please try again'));
            }
    }
}


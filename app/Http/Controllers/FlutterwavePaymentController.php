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

class FlutterwavePaymentController extends Controller
{
    public $secret_key;
    public $public_key;
    public $is_enabled;


    public function paymentConfig()
    {
        $user = \Auth::user();
        if(\Auth::check())
        {
            $payment_setting = Utility::getAdminPaymentSetting();
        }
        else
        {
            $payment_setting = Utility::getCompanyPaymentSettingWithOutAuth($user->id);
        }

        $this->secret_key = isset($payment_setting['flutterwave_secret_key']) ? $payment_setting['flutterwave_secret_key'] : '';
        $this->public_key = isset($payment_setting['flutterwave_public_key']) ? $payment_setting['flutterwave_public_key'] : '';
        $this->is_enabled = isset($payment_setting['is_flutterwave_enabled']) ? $payment_setting['is_flutterwave_enabled'] : 'off';

        return $this;
    }


    public function planPayWithFlutterwave(Request $request)
    {
        $planID    = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $plan      = Plan::find($planID);
        $authuser  = Auth::user();
        $coupon_id = '';
        if($plan)
        {
            $price = $plan->price;
            if(isset($request->coupon) && !empty($request->coupon))
            {
                $request->coupon = trim($request->coupon);
                $coupons         = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();

                if(!empty($coupons))
                {
                    $usedCoupun             = $coupons->used_coupon();
                    $discount_value         = ($price / 100) * $coupons->discount;
                    $plan->discounted_price = $price - $discount_value;

                    if($usedCoupun >= $coupons->limit)
                    {
                        return redirect()->back()->with('error', __('This coupon code has expired.'));
                    }
                    $price     = $price - $discount_value;
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
                            'price' => $price == null ? 0 : $price,
                            'price_currency' => !empty(Utility::getAdminCurrency()) ? Utility::getAdminCurrency() : 'usd',
                            'txn_id' => '',
                            'payment_type' => __('Flutterwave'),
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

                    return Utility::error_res(__('Plan fail to upgrade.'));
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
            return Utility::error_res(__('Plan is deleted.'));
        }

    }

    public function getPaymentStatus(Request $request, $pay_id, $plan)
    {
        $user = Auth::user();
        $payment = $this->paymentConfig($user);
        $planID  = \Illuminate\Support\Facades\Crypt::decrypt($plan);
        $plan    = Plan::find($planID);
        $result  = array();

        $user = Auth::user();
        if($plan)
        {
            try
            {
                $orderID = time();
                $data    = array(
                    'txref' => $pay_id,
                    'SECKEY' => $this->secret_key,
                    //secret key from pay button generated on rave dashboard
                );
                // make request to endpoint using unirest.
                $headers = array('Content-Type' => 'application/json');
                $body    = \Unirest\Request\Body::json($data);
                $url     = "https://api.ravepay.co/flwv3-pug/getpaidx/api/v2/verify"; //please make sure to change this to production url when you go live

                // Make `POST` request and handle response with unirest
                $response = \Unirest\Request::post($url, $headers, $body);
                if(!empty($response))
                {
                    $response = json_decode($response->raw_body, true);
                }
                if(isset($response['status']) && $response['status'] == 'success')
                {
                    $paydata = $response['data'];

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
                    $order->price          = isset($paydata['amount']) ? $paydata['amount'] : 0;
                    $order->price_currency = Utility::getAdminCurrency();
                    $order->txn_id         = isset($paydata['txid']) ? $paydata['txid'] : $pay_id;
                    $order->payment_type   = __('Flutterwave');
                    $order->payment_status = 'success';
                    $order->receipt        = '';
                    $order->user_id        = $user->id;
                    $order->save();


                    $referral = DB::table('referral_programs')->first();
                    $amount=  ($plan->price * $referral->commission) /100;
                    $referral = DB::table('referral_programs')->first();
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
                        return redirect()->route('plan.index')->with('success', __('Plan activated Successfully!'));
                    }
                    else
                    {
                        return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
                    }
                }
                else
                {
                    return redirect()->route('plan.index')->with('error', __('Transaction has been failed! '));
                }
            }
            catch(\Exception $e)
            {
                return redirect()->route('plan.index')->with('error', __('Plan not found!'));
            }
        }
    }

    public function invoicePayWithFlutterwave(Request $request)
    {

        $invoiceID = $request->invoice_id;
        $invoiceID = \Crypt::decrypt($invoiceID);
        $invoice   = Invoice::find($invoiceID);

        if(\Auth::check())
        {
            $user=\Auth::user();
        }
        else
        {
            $user= User::where('id',$invoice->created_by)->first();
        }

        if($invoice)
        {
            $price = $request->amount;
            if($price > 0)
            {
                $res_data['email']       = $user->email;
                $res_data['total_price'] = $price;
                $res_data['currency']    = Utility::settingsById($invoice->created_by)['site_currency'];
                $res_data['flag']        = 1;

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

    public function getInvoicePaymentStatus(Request $request,$invoice_id,$pay_id)
    {

        $invoiceID = \Illuminate\Support\Facades\Crypt::decrypt($invoice_id);
        $invoice = Invoice::find($invoiceID);
       //$user = \Auth::user();
        if(\Auth::check())
        {
            $user=\Auth::user();
        }
        else
        {
            $user= User::where('id',$invoice->created_by)->first();
       }

        $orderID   = strtoupper(str_replace('.', '', uniqid('', true)));
        $settings  = DB::table('settings')->where('created_by', '=', $user->creatorId())->get()->pluck('value', 'name');

        //$payment   = $this->paymentConfig($user);
        // $invoiceID = \Illuminate\Support\Facades\Crypt::decrypt($invoice_id);
        // $invoice   = Invoice::find($invoiceID);
        $result    = array();

        if($invoice)
        {
            // try
            // {

                $data = array(
                    'txref' => $pay_id,
                    'SECKEY' => $this->secret_key,
                    //secret key from pay button generated on rave dashboard
                );
                // make request to endpoint using unirest.
                $headers = array('Content-Type' => 'application/json');
                $body    = \Unirest\Request\Body::json($data);
                $url     = "https://api.ravepay.co/flwv3-pug/getpaidx/api/v2/verify"; //please make sure to change this to production url when you go live

                // Make `POST` request and handle response with unirest
                $response = \Unirest\Request::post($url, $headers, $body);
                if(!empty($response))
                {
                    $response = json_decode($response->raw_body, true);
                }
                // if(isset($response['status']) && $response['status'] == 'success')
                // {
                        $paydata = $response['data'];
                        $payments = New InvoicePayment;
                        $payments->transaction = $orderID;
                        $payments->invoice = $invoice->id;
                        $payments->amount = $request->amount;
                        $payments->date = date('Y-m-d');
                        $payments->payment_method = __('FlutterWave');
                        $payments->payment_type = __('FlutterWave');
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
                    //     $msg = __('New payment of ').$amount.' '.__('created for ').$user->name.__(' by FlutterWave').'.';
                    //     Utility::send_slack_msg($msg);
                    // }
                    if (isset($settings['payment_create_notification']) && $settings['payment_create_notification'] == 1) {
                        $uArr = [
                            'user_name' => $user->name,
                            'amount'=> $request->amount,
                            'created_by'=> 'by Flutterwave',
                        ];
                        Utility::send_slack_msg('new_payment', $uArr);
                        }
                    if (isset($settings['telegram_payment_create_notification']) && $settings['telegram_payment_create_notification'] == 1) {
                        $uArr = [
                            'user_name' => $user->name,
                            'amount'=> $request->amount,
                            'created_by'=> 'by Flutterwave',
                        ];
                        Utility::send_telegram_msg('new_payment', $uArr);
                        }
                    if (isset($settings['twilio_invoice_payment_create_notification']) && $settings['twilio_invoice_payment_create_notification'] == 1) {
                        $uArr = [
                            'user_name' => $user->name,
                            'amount'=> $request->amount,
                            'created_by'=> 'by Flutterwave',
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
                            if($status == true)
                            {
                                return redirect()->back()->with('success', __('Payment added Successfully!'));
                            }
                            else
                            {
                                return redirect()->back()->with('error', __('Webhook call failed.'));
                            }
                        }
                    // if(isset($settings['telegram_payment_create_notification']) && $settings['telegram_payment_create_notification'] ==1){
                    //         $resp =__('New payment of ').$amount.' '.__('created for ').$user->name.__(' by FlutterWave').'.';
                    //         Utility::send_telegram_msg($resp);
                    // }
                    // $client_namee = Client::where('user_id',$invoice->client)->first();
                    // if(isset($settings['twilio_invoice_payment_create_notification']) && $settings['twilio_invoice_payment_create_notification'] ==1)
                    // {
                    //      $message = __('New payment of ').$amount.' '.__('created for ').$user->name.__(' by FlutterWave').'.';

                    //      Utility::send_twilio_msg($client_namee->mobile,$message);
                    // }
                    return redirect()->back()->with('success', __(' Payment successfully added.'));

                // }
                // else
                // {
                //     return redirect()->back()->with('error', __('Transaction has been failed! '));
                // }
            // }
            // catch(\Exception $e)
            // {
            //     return redirect()->back()->with('error', __('Invoice not found!'));
            // }
        }
    }
}

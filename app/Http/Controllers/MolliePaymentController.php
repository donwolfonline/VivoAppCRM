<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Order;
use App\Models\Plan;
use App\Models\UserCoupon;
use App\Models\Utility;
use App\Models\Client;
use App\Models\transactions;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MolliePaymentController extends Controller
{

    public $api_key;
    public $profile_id;
    public $partner_id;
    public $is_enabled;

    public function paymentConfig($user)
    {
        // if(Auth::check()){
        //     $user = \Auth::user();
        // }

        if(\Auth::check())
        {
            $payment_setting = Utility::getAdminPaymentSetting();
        }
        else
        {
            $payment_setting = Utility::getCompanyPaymentSettingWithOutAuth($user->id);
        }
        $this->api_key    = isset($payment_setting['mollie_api_key']) ? $payment_setting['mollie_api_key'] : '';
        $this->profile_id = isset($payment_setting['mollie_profile_id']) ? $payment_setting['mollie_profile_id'] : '';
        $this->partner_id = isset($payment_setting['mollie_partner_id']) ? $payment_setting['mollie_partner_id'] : '';
        $this->is_enabled = isset($payment_setting['is_mollie_enabled']) ? $payment_setting['is_mollie_enabled'] : 'off';

        return $this;
    }


    public function planPayWithMollie(Request $request)
    {
        $authuser   = Auth::user();
        $payment    = $this->paymentConfig($authuser);
        $planID     = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $plan       = Plan::find($planID);

        $coupons_id = '';
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
                    $coupons_id             = $coupons->id;
                    if($usedCoupun >= $coupons->limit)
                    {
                        return redirect()->back()->with('error', __('This coupon code has expired.'));
                    }
                    $price = $price - $discount_value;
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

                    $orderID = time();
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
                            'price_currency' => Utility::getAdminCurrency(),
                            'txn_id' => '',
                            'payment_type' => __('Mollie'),
                            'payment_status' => 'succeeded',
                            'receipt' => null,
                            'user_id' => $authuser->id,
                        ]
                    );
                    $assignPlan = $authuser->assignPlan($plan->id);

                    return redirect()->route('plan.index')->with('success', __('Plan activated Successfully!'));
                }
                else
                {
                    return redirect()->back()->with('error', __('Plan fail to upgrade.'));
                }
            }
        try
         {
            $mollie = new \Mollie\Api\MollieApiClient();
            $mollie->setApiKey($this->api_key);

            $payment = $mollie->payments->create(
                [
                    "amount" => [
                        "currency" => Utility::getAdminCurrency(),
                        "value" => number_format($price, 2),
                    ],
                    "description" => "payment for product",
                    "redirectUrl" => route(
                        'plan.mollie', [
                                         $request->plan_id,
                                         'coupon_id=' . $coupons_id,
                                     ]
                    ),
                ]
            );


            session()->put('mollie_payment_id', $payment->id);

            return redirect($payment->getCheckoutUrl())->with('payment_id', $payment->id);
        }
        catch(\Exception $e)
        {


            return redirect()->route('plan.index')->with('error', __('There is not enough money in your account.'));
        }
        }
        else
        {
            return redirect()->back()->with('error', 'Plan is deleted.');
        }

    }

    public function getPaymentStatus(Request $request, $plan)
    {
        $user=\Auth::user();
        $payment = $this->paymentConfig($user);

        $planID  = \Illuminate\Support\Facades\Crypt::decrypt($plan);
        $plan    = Plan::find($planID);
        $user    = Auth::user();
        $orderID = time();
        if($plan)
        {
            try
            {
                $mollie = new \Mollie\Api\MollieApiClient();
                $mollie->setApiKey($this->api_key);

                if(session()->has('mollie_payment_id'))
                {
                    $payment = $mollie->payments->get(session()->get('mollie_payment_id'));

                    if($payment->isPaid())
                    {

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
                        $order->price          = $plan->price;
                        $order->price_currency = Utility::getAdminCurrency();
                        $order->txn_id         = isset($request->TXNID) ? $request->TXNID : '';
                        $order->payment_type   = __('Mollie');
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


                        $assignPlan = $user->assignPlan($plan->id, $request->payment_frequency);

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



    public function invoicePayWithMollie(Request $request)
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

        if(Auth::check()){
            $payment_setting = Utility::getCompanyPaymentSettingWithOutAuth($invoice->created_by);

            $this->api_key    = isset($payment_setting['mollie_api_key']) ? $payment_setting['mollie_api_key'] : '';
            $this->profile_id = isset($payment_setting['mollie_profile_id']) ? $payment_setting['mollie_profile_id'] : '';
            $this->partner_id = isset($payment_setting['mollie_partner_id']) ? $payment_setting['mollie_partner_id'] : '';
            $this->is_enabled = isset($payment_setting['is_mollie_enabled']) ? $payment_setting['is_mollie_enabled'] : 'off';
            $settings = Utility::settingsById($invoice->created_by);
            //dd($payment_setting);
        }else{
             $payment = $this->paymentConfig();

            $settings  = DB::table('settings')->where('created_by', '=', $user->creatorId())->get()->pluck('value', 'name');
        }
        //dd($settings);
        // dd(Utility::settingsById($invoice->created_by)['site_currency']);

        if($invoice)
        {
            $price = $request->amount;
             if($price > 0)
            {

                $mollie = new \Mollie\Api\MollieApiClient();
                $mollie->setApiKey($this->api_key);
                // dd($mollie);

                // $payment = $mollie->payments->create(
                //     [
                //         "amount" => [
                //             "currency" => Utility::settingsById($invoice->created_by)['site_currency'],

                //         "value" => number_format((float)$price, 2, '.', ''),

                //         ],

                //         "description" => "payment for product",
                //         "redirectUrl" => route(
                //             'invoice.mollie', [
                //                                 $request->invoice_id,
                //                                 $price,
                //                                 ]),
                //     ]
                // );
                $payment = $mollie->payments->create(
                    [
                        "amount" => [
                            "currency" => Utility::settingsById($invoice->created_by)['site_currency'],
                            "value" => number_format((float)$price, 2, '.', ''),
                        ],
                        "description" => "payment for Invoice",
                        "redirectUrl" => route('invoice.mollie',[$request->invoice_id,'amount'=>(float)$request->amount]),
                    ]
                );

                session()->put('mollie_payment_id', $payment->id);

                return redirect($payment->getCheckoutUrl())->with('payment_id', $payment->id);

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
            return redirect()->back()->with('error', 'Invoice is deleted.');
        }

    }

    public function getInvoicePaymentStatus( $invoice_id, $amount)
    {

        $invoiceID = \Illuminate\Support\Facades\Crypt::decrypt($invoice_id);

        $invoice = Invoice::find($invoiceID);
       $user = \Auth::user();
        if(\Auth::check())
        {
            $user=\Auth::user();
        }
        else
        {
            $user= User::where('id',$invoice->created_by)->first();

        }

        $orderID   = strtoupper(str_replace('.', '', uniqid('', true)));

        if (Auth::check()) {
            $payment_setting = Utility::getNonAuthCompanyPaymentSetting($invoice->created_by);
            $this->api_key    = isset($payment_setting['mollie_api_key']) ? $payment_setting['mollie_api_key'] : '';
            $this->profile_id = isset($payment_setting['mollie_profile_id']) ? $payment_setting['mollie_profile_id'] : '';
            $this->partner_id = isset($payment_setting['mollie_partner_id']) ? $payment_setting['mollie_partner_id'] : '';
            $this->is_enabled = isset($payment_setting['is_mollie_enabled']) ? $payment_setting['is_mollie_enabled'] : 'off';
            $settings = Utility::settingsById($invoice->created_by);

        } else {
            //$payment = $this->paymentConfig($user);
            $settings = DB::table('settings')->where('created_by', '=', $user->creatorId())->get()->pluck('value', 'name');
        }

        $result    = array();


        if($invoice)
        {
            // try
            // {
                $mollie = new \Mollie\Api\MollieApiClient();
                $mollie->setApiKey($this->api_key);
                if(session()->has('mollie_payment_id'))
                {
                    $payment = $mollie->payments->get(session()->get('mollie_payment_id'));
                  ;

                    if($payment->isPaid())
                    {
                            $payments = New InvoicePayment;
                            $payments->transaction = $orderID;
                            $payments->invoice = $invoice->id;
                            $payments->amount = $amount;
                            $payments->date = date('Y-m-d');
                            $payments->payment_method = __('Mollie');
                            $payments->payment_type = __('Mollie');
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
                        //     $msg = __('New payment of ').$amount.' '.__('created for ').$user->name.__(' by Mollie').'.';
                        //     Utility::send_slack_msg($msg);
                        // }
                        if (isset($settings['payment_create_notification']) && $settings['payment_create_notification'] == 1) {
                            $uArr = [
                                'user_name' => $user->name,
                                'amount'=> $amount,
                                'created_by'=> 'by Mollie',
                            ];
                            Utility::send_slack_msg('new_payment', $uArr);
                            }
                        if (isset($settings['telegram_payment_create_notification']) && $settings['telegram_payment_create_notification'] == 1) {
                            $uArr = [
                                'user_name' => $user->name,
                                'amount'=> $amount,
                                'created_by'=> 'by Mollie',
                            ];
                            Utility::send_telegram_msg('new_payment', $uArr);
                            }
                        if (isset($settings['twilio_invoice_payment_create_notification']) && $settings['twilio_invoice_payment_create_notification'] == 1) {
                            $uArr = [
                                'user_name' => $user->name,
                                'amount'=> $amount,
                                'created_by'=> 'by Mollie',
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
                        //         $resp =__('New payment of ').$amount.' '.__('created for ').$user->name.__(' by Mollie').'.';
                        //         Utility::send_telegram_msg($resp);
                        // }
                        // $client_namee = Client::where('user_id',$invoice->client)->first();
                        // if(isset($settings['twilio_invoice_payment_create_notification']) && $settings['twilio_invoice_payment_create_notification'] ==1)
                        // {
                        //      $message = __('New payment of ').$amount.' '.__('created for ').$user->name.__(' by Mollie').'.';
                        //      //dd($message);
                        //      Utility::send_twilio_msg($client_namee->mobile,$message);
                        // }

                        // return redirect()->route('pay.invoice',$invoice_id)->with('success', __(' Payment successfully added.'));
                        return redirect()->route('pay.invoice',$invoice_id)->with('success', __('Invoice paid Successfully!'));

                    }
                    else
                    {
                        return redirect()->back()->with('error', __('Transaction has been failed! '));
                    }
                }
                else
                {
                    return redirect()->back()->with('error', __('Transaction has been failed! '));
                }
            }
        //     catch(\Exception $e)
        //     // {
        //     //     return redirect()->back()->with('error', __('Invoice not found!'));
        //     // }
        // }
    }


}

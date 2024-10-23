<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Utility;
use App\Models\Client;
use App\Models\transactions;
use App\Models\User;
use App\Models\UserCoupon;
use CoinGate\CoinGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CoingatePaymentController extends Controller
{
    //


    public $mode;
    public $coingate_auth_token;
    public $is_enabled;

    public function paymentConfig($user)
    {
        // dd($this->coingate_auth_token);
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


        $this->coingate_auth_token = isset($payment_setting['coingate_auth_token']) ? $payment_setting['coingate_auth_token'] : '';
        $this->mode                = isset($payment_setting['coingate_mode']) ? $payment_setting['coingate_mode'] : 'off';
        $this->is_enabled          = isset($payment_setting['is_coingate_enabled']) ? $payment_setting['is_coingate_enabled'] : 'off';

        return $this;
    }


    public function planPayWithCoingate(Request $request)
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
                            'price_currency' => !empty(Utility::getAdminCurrency()) ? Utility::getAdminCurrency() : 'USD',
                            'txn_id' => '',
                            'payment_type' => 'coingate',
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
            CoinGate::config(
                array(
                    'environment' => $this->mode,
                    'auth_token' => $this->coingate_auth_token,
                    'curlopt_ssl_verifypeer' => FALSE,

                )
            );

            $post_params = array(
                'order_id' => time(),
                'price_amount' => $price,
                'price_currency' => Utility::getAdminCurrency(),
                'receive_currency' => Utility::getAdminCurrency(),
                'callback_url' => route(
                    'plan.coingate', [
                                       $request->plan_id,
                                       'coupon_id=' . $coupons_id,
                                   ]
                ),
                'cancel_url' => route('stripe', [$request->plan_id]),
                'success_url' => route(
                    'plan.coingate', [
                                       $request->plan_id,
                                       'coupon_id=' . $coupons_id,
                                   ]
                ),
                'title' => 'Plan #' . time(),
            );

            $order = \CoinGate\Merchant\Order::create($post_params);
            // dd($order);

            if($order)
            {
                return redirect($order->payment_url);
            }
            else
            {
                return redirect()->back()->with('error', __('opps something wren wrong.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', 'Plan is deleted.');
        }

    }

    public function coingatePlanGetPayment(Request $request)
    {
        $user                  = Auth::user();
        $plan_id               = $request->plan_id;
        $store_id              = Auth::user()->current_store;
        $admin_payment_setting = Utility::getAdminPaymentSetting();
        $plan                  = Plan::find($plan_id);
        $price                 = $plan->price;
        if($plan)
        {
                $orderID = time();
                if($request->has('coupon_id') && $request->coupon_id != '')
                {
                    $coupons = Coupon::find($request->coupon_id);
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
                }
                $order                 = new Order();
                $order->order_id       = $orderID;
                $order->name           = $user->name;
                $order->card_number    = '';
                $order->card_exp_month = '';
                $order->card_exp_year  = '';
                $order->plan_name      = $plan->name;
                $order->plan_id        = $plan->id;
                $order->price          = $price;
                $order->price_currency = env('CURRENCY_CODE');
                $order->txn_id         = isset($request->transaction_id) ? $request->transaction_id : '';
                $order->payment_type   = __('Coingate');
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
            return redirect()->route('plan.index')->with('error', __('Plan is deleted.'));
        }
    }

    public function getPaymentStatus(Request $request,$plan)
    {

        $user                  = Auth::user();
        $planID  = \Illuminate\Support\Facades\Crypt::decrypt($plan);
        $plan_id               = $planID;
        $store_id              = \Auth::user()->current_store;
        $admin_payment_setting = Utility::getAdminPaymentSetting();
        $plan                  = Plan::find($plan_id);
        $price                 = $plan->price;
        if($plan)
        {

                $orderID = time();

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
                }
                $order                 = new Order();
                $order->order_id       = $orderID;
                $order->name           = $user->name;
                $order->card_number    = '';
                $order->card_exp_month = '';
                $order->card_exp_year  = '';
                $order->plan_name      = $plan->name;
                $order->plan_id        = $plan->id;
                $order->price          = $price;
                $order->price_currency = Utility::getAdminCurrency();
                $order->txn_id         = isset($request->transaction_id) ? $request->transaction_id : '';
                $order->payment_type   = __('Coingate');
                $order->payment_status = 'success';
                $order->receipt        = '';
                $order->user_id        = $user->id;
                $order->save();
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
            return redirect()->route('plan.index')->with('error', __('Plan is deleted.'));
        }
    }
    public function invoicePayWithCoingate(Request $request)
    {
        // dd($request->all());
        try {
            $invoiceID = \Crypt::decrypt($request->invoice_id);

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Invoice Not Found.'));
        }
        // $invoiceID = $request->invoice_id;
        // $invoiceID = \Crypt::decrypt($invoiceID);
        $invoice = Invoice::find($invoiceID);
        if(\Auth::check())
        {
            $user=\Auth::user();
        }
        else
        {
            $user= User::where('id',$invoice->created_by)->first();
        }
        $payment   = $this->paymentConfig($user);
        $invoice   = Invoice::find($invoiceID);
        $settings  = DB::table('settings')->where('created_by', '=', $user->creatorId())->get()->pluck('value', 'name');

        if($invoice)

        {
            $price = $request->amount;
            if($price > 0)
            {
                CoinGate::config(
                    array(
                        'environment' => $this->mode,
                        'auth_token' => $this->coingate_auth_token,
                        'curlopt_ssl_verifypeer' => FALSE,
                    )
                );
                $post_params = array(
                    'order_id' => time(),
                    'price_amount' => $price,
                    'price_currency' => Utility::getValByName('site_currency'),
                    'receive_currency' => Utility::getValByName('site_currency'),
                    'callback_url' => route(
                        'invoice.coingate', [
                                           $request->invoice_id,
                                           $price,
                                       ]
                    ),
                    'cancel_url' => route('invoice.coingate', [$request->invoice_id,'']),
                    'success_url' => route(
                        'invoice.coingate', [
                                           $request->invoice_id,
                                           $price,
                                       ]
                    ),
                    'title' => __('Invoice') . ' ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id),
                );

                $order = \CoinGate\Merchant\Order::create($post_params);
                if($order)
                {
                    return redirect($order->payment_url);
                }
                else
                {
                    return redirect()->back()->with('error', __('opps something wren wrong.'));
                }

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

    public function getInvoicePaymentStatus(Request $request, $invoice_id, $amount)
    {
        $invoice_id = \Crypt::decrypt($invoice_id);
        $invoice = Invoice::find($invoice_id);
        if(\Auth::check())
        {
            $user=\Auth::user();
        }
        else
        {
            $user= User::where('id',$invoice->created_by)->first();
        }

        $orderID   = strtoupper(str_replace('.', '', uniqid('', true)));
        $settings  = DB::table('settings')->where('created_by', '=', $invoice->created_by)->get()->pluck('value', 'name');

        $result    = array();
        if($invoice)
        {
            $payments = New InvoicePayment;
            $payments->transaction = $orderID;
            $payments->invoice = $invoice->id;
            $payments->amount = $amount;
            $payments->date = date('Y-m-d');
            $payments->payment_method = 1;
            $payments->payment_type = __('Coingate');
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
            //     $msg = __('New payment of ').$amount.' '.__('created for ').$user->name.__(' by Coingate').'.';
            //     Utility::send_slack_msg($msg);
            // }
            if (isset($settings['payment_create_notification']) && $settings['payment_create_notification'] == 1) {
                $uArr = [
                    'user_name' => $user->name,
                    'amount'=> $amount,
                    'created_by'=> 'by Coingate',
                ];
                Utility::send_slack_msg('new_payment', $uArr);
                }
                if (isset($settings['telegram_payment_create_notification']) && $settings['telegram_payment_create_notification'] == 1) {
                    $uArr = [
                        'user_name' => $user->name,
                        'amount'=> $amount,
                        'created_by'=> 'by Coingate',
                    ];
                    Utility::send_telegram_msg('new_payment', $uArr);
                    }
                if (isset($settings['twilio_invoice_payment_create_notification']) && $settings['twilio_invoice_payment_create_notification'] == 1) {
                    $uArr = [
                        'user_name' => $user->name,
                        'amount'=> $amount,
                        'created_by'=> 'by Coingate',
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
                //         $resp = __('New payment of ').$amount.' '.__('created for ').$user->name.__(' by Coingate').'.';
                //         Utility::send_telegram_msg($resp);
                // }

            // $client_namee = Client::where('user_id',$invoice->client)->first();
            // if(isset($settings['twilio_invoice_payment_create_notification']) && $settings['twilio_invoice_payment_create_notification'] ==1)
            // {
            //      $message = __('New payment of ').$amount.' '.__('created for ').$user->name.__(' by Coingate').'.';
            //      Utility::send_twilio_msg($client_namee->mobile,$message);
            // }
            if(\Auth::check())
            {
                return redirect()->back()->with('success', __(' Payment successfully added.'));
            }
            else
            {
                return redirect()->route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('success', __(' Payment successfully added.'));
            }


        }
        else{
            if(\Auth::check())
                {
                    return redirect()->back()->with('success', __(' Payment successfully added.'));
                }
                else
                {
                    return redirect()->route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('success', __(' Payment successfully added.'));
                }

                return redirect()->back()->with('error', __('Invoice is deleted.'));
            }
    }
}

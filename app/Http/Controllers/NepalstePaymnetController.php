<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utility;
use App\Models\Plan;
use App\Models\Coupon;
use App\Models\UserCoupon;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\User;
use App\Models\InvoicePayment;
use App\Models\Customer;
use Auth;

class NepalstePaymnetController extends Controller
{
    public function planPayWithnepalste(Request $request)
    {

        $payment_setting = Utility::getAdminPaymentSetting();
        $api_key = isset($payment_setting['nepalste_public_key']) ? $payment_setting['nepalste_public_key'] : '';
        $currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : '';
        $planID = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);

        $plan = Plan::find($planID);
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        $user = Auth::user();

        if ($plan) {
            $get_amount = $plan->price;

            if (!empty($request->coupon))
            {
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

                            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                            $userCoupon = new UserCoupon();

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
                                    'price_currency' => $currency,
                                    'txn_id' => '',
                                    'payment_type' => 'Nepalste',
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
        }
        if (!empty($request->coupon))
        {
            $response = ['get_amount' => $get_amount, 'plan' => $plan , 'coupon_id'=>$coupons->id];
        }
        else{
            $response = ['get_amount' => $get_amount, 'plan' => $plan];
        }

        $parameters = [
            'identifier' => 'DFU80XZIKS',
            'currency' => $currency,
            'amount' => $get_amount,
            'details' => $plan->name,
            'ipn_url' => route('nepalste.status',$response),
            'cancel_url' => route('nepalste.cancel'),
            'success_url' => route('nepalste.status',$response),
            'public_key' => $api_key,
            'site_logo' => 'https://nepalste.com.np/assets/images/logoIcon/logo.png',
            'checkout_theme' => 'dark',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@mail.com',
        ];

         //live end point
         $liveUrl = "https://nepalste.com.np/payment/initiate";

         //test end point
         $sandboxUrl = "https://nepalste.com.np/sandbox/payment/initiate";

         $url = $payment_setting['nepalste_mode'] == 'live' ? $liveUrl : $sandboxUrl;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS,  $parameters);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result, true);

        if(isset($result['success'])){
            return redirect($result['url']);
        }else{
            return redirect()->back()->with('error',__($result['message']));
        }
    }

    public function planGetNepalsteStatus(Request $request)
    {
        $payment_setting = Utility::getAdminPaymentSetting();
        $currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : '';

        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

        $getAmount = $request->get_amount;
        $authuser = \Auth::user();
        $plan = Plan::find($request->plan);

            $order = new Order();
            $order->order_id = $orderID;
            $order->name = $authuser->name;
            $order->card_number = '';
            $order->card_exp_month = '';
            $order->card_exp_year = '';
            $order->plan_name = $plan->name;
            $order->plan_id = $plan->id;
            $order->price = $getAmount;
            $order->price_currency = $currency;
            $order->txn_id = $orderID;
            $order->payment_type = __('Neplaste');
            $order->payment_status = 'success';
            $order->txn_id = '';
            $order->receipt = '';
            $order->user_id = $authuser->id;
            $order->save();
            $assignPlan = $authuser->assignPlan($plan->id);

            $coupons = Coupon::find($request->coupon_id);
            if (!empty($request->coupon_id)) {
                if (!empty($coupons)) {
                    $userCoupon = new UserCoupon();
                    $userCoupon->user = $authuser->id;
                    $userCoupon->coupon = $coupons->id;
                    $userCoupon->order = $orderID;
                    $userCoupon->save();
                    $usedCoupun = $coupons->used_coupon();
                    if ($coupons->limit <= $usedCoupun) {
                        $coupons->is_active = 0;
                        $coupons->save();
                    }
                }
            }

            if ($assignPlan['is_success'])
            {
                return redirect()->route('plan.index')->with('success', __('Plan activated Successfully.'));
            } else
            {
                return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
            }
    }

    public function planGetNepalsteCancel(Request $request)
    {
        return redirect()->back()->with('error',__('Transaction has failed'));
    }


    public function invoicePayWithnepalste(Request $request)
    {


        $invoice_id = \Illuminate\Support\Facades\Crypt::decrypt($request->invoice_id);
        $invoice = Invoice::find($invoice_id);
        $user = User::where('id', $invoice->created_by)->first();
        $get_amount = $request->amount;
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

            if ($invoice) {
                $payment_setting = Utility::getCompanyPaymentSetting($user->id);
                $api_key = $payment_setting['nepalste_public_key'];
                $settings = Utility::settingsById($user->id);
                $currency = isset($settings['site_currency']) ? $settings['site_currency'] : '';

                    $response = ['user' => $user, 'get_amount' => $get_amount, 'invoice' => $invoice];

                $parameters = [
                    'identifier' => 'DFU80XZIKS',
                    'currency' => $currency,
                    'amount' => $get_amount,
                    'details' => 'Invoice',
                    'ipn_url' => route('invoice.nepalste.status',$response),
                    'cancel_url' => route('invoice.nepalste.cancel'),
                    'success_url' => route('invoice.nepalste.status',$response),
                    'public_key' => $api_key,
                    'site_logo' => 'https://nepalste.com.np/assets/images/logoIcon/logo.png',
                    'checkout_theme' => 'dark',
                    'customer_name' => 'John Doe',
                    'customer_email' => 'john@mail.com',
                ];

                //live end point
                $liveUrl = "https://nepalste.com.np/payment/initiate";

                //test end point
                $sandboxUrl = "https://nepalste.com.np/sandbox/payment/initiate";

                $url = $payment_setting['nepalste_mode'] == 'live' ? $liveUrl : $sandboxUrl;

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POSTFIELDS,  $parameters);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                curl_close($ch);

                $result = json_decode($result, true);

                if(isset($result['success'])){
                    return redirect($result['url']);
                }else{
                    return redirect()->back()->with('error',__($result['message']));
                }
    }
}

    public function invoiceGetNepalsteStatus(Request $request)
    {

        $invoice = Invoice::find($request->invoice);
        $user = User::where('id', $invoice->created_by)->first();
        $settings= Utility::settingsById($invoice->created_by);
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

        $payment_setting = Utility::getCompanyPaymentSetting($user->id);
        $get_amount = $request->get_amount;

            $invoice_payment                 = new InvoicePayment();
            $invoice_payment->transaction       = $orderID;
            $invoice_payment->invoice     = $invoice->id;
            $invoice_payment->amount         = $get_amount;
            $invoice_payment->date           = Date('Y-m-d');
            $invoice_payment->payment_method = __('Nepalste');;
            $invoice_payment->payment_type   = 'Nepalste';
            $invoice_payment->receipt        = '';
            $invoice_payment->created_by = $user->creatorId();
            $invoice_payment->notes    =  __('Invoice') .' ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id);
            $invoice_payment->save();


            if($invoice->getDue() <= 0)
            {
                $invoice->status = 5;
                $invoice->save();
            }
            elseif(($invoice->getDue() - $invoice_payment->amount) == 0)
            {
                $invoice->status = 4;
                $invoice->save();
            }
            else
            {
                $invoice->status = 3;
                $invoice->save();
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
                        'amount'=> $get_amount,
                        'created_by'=> 'by Mollie',
                    ];
                    Utility::send_slack_msg('new_payment', $uArr);
                }
                if (isset($settings['telegram_payment_create_notification']) && $settings['telegram_payment_create_notification'] == 1) {
                    $uArr = [
                        'user_name' => $user->name,
                        'amount'=> $get_amount,
                        'created_by'=> 'by Mollie',
                    ];
                    Utility::send_telegram_msg('new_payment', $uArr);
                }
                if (isset($settings['twilio_invoice_payment_create_notification']) && $settings['twilio_invoice_payment_create_notification'] == 1) {
                    $uArr = [
                        'user_name' => $user->name,
                        'amount'=> $get_amount,
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

                    return redirect()->back()->with('success', __('Invoice paid Successfully!'));
            }

    public function invoiceGetNepalsteCancel(Request $request)
    {
        return redirect()->back()->with('error',__('Transaction has failed'));
    }
}

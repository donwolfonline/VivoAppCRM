<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\User;
use App\Models\UserCoupon;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Plan;
use App\Models\Order;
use App\Models\transactions;
use Illuminate\Support\Facades\DB;

class MidtransController extends Controller
{

    public function planPayWithMidtrans(Request $request)
    {
        $payment_setting    = Utility::getAdminPaymentSetting();
        $midtrans_secret    = $payment_setting['midtrans_secret'];
        $midtrans_mode = $payment_setting['midtrans_mode'];
        $currency           = Utility::getAdminCurrency();
        $authuser = Auth::user();

        $planID     = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $plan       = Plan::find($planID);
        $orderID    = strtoupper(str_replace('.', '', uniqid('', true)));

        try{
        if ($plan) {
            $get_amount = round($plan->price);

            if (!empty($request->coupon)) {
                $coupons = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                if (!empty($coupons)) {
                    $usedCoupun         = $coupons->used_coupon();
                    $discount_value     = ($plan->price / 100) * $coupons->discount;
                    $get_amount         = $plan->price - $discount_value;
                    $userCoupon         = new UserCoupon();
                    $userCoupon->user   = Auth::user()->id;
                    $userCoupon->coupon = $coupons->id;
                    $userCoupon->order  = $orderID;
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

                    // $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                    Order::create(
                        [
                            'order_id'      => $orderID,
                            'name'          => null,
                            'email'         => null,
                            'card_number'   => null,
                            'card_exp_month' => null,
                            'card_exp_year' => null,
                            'plan_name'     => $plan->name,
                            'plan_id'       => $plan->id,
                            'price'         => $get_amount == null ? 0 : $get_amount,
                            'price_currency' => $currency,
                            'txn_id'        => '',
                            'payment_type'  => __('Zero Price'),
                            'payment_status' => 'succeeded',
                            'receipt'       => null,
                            'user_id'       => $authuser->id,
                        ]
                    );

                    return redirect()->route('plan.index')->with('success', __('Plan activated Successfully!'));
                }
                else
                {
                    return redirect()->back()->with('error', __('Plan fail to upgrade.'));
                }
            }


            // Set your Merchant Server Key
            \Midtrans\Config::$serverKey    = $midtrans_secret;
            if($midtrans_mode == 'sandbox')
            {
                 // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
                \Midtrans\Config::$isProduction = false;
            }
            else{
                \Midtrans\Config::$isProduction = false;

            }
            // Set sanitization on (default)
            \Midtrans\Config::$isSanitized  = true;
            // Set 3DS transaction for credit card to true
            \Midtrans\Config::$is3ds = true;

            $params = array(
                'transaction_details'   => array(
                    'order_id'          => $orderID,
                    'gross_amount'      => $get_amount,
                ),
                'customer_details' => array(
                    'first_name'        => Auth::user()->name,
                    'last_name'         => '',
                    'email'             => Auth::user()->email,
                    'phone'             => '8787878787',
                ),
            );
            $snapToken = \Midtrans\Snap::getSnapToken($params);

            $authuser->plan = $plan->id;
            $authuser->save();

            Order::create(
                [
                    'order_id'          => $orderID,
                    'name'              => null,
                    'email'             => null,
                    'card_number'       => null,
                    'card_exp_month'    => null,
                    'card_exp_year'     => null,
                    'plan_name'         => $plan->name,
                    'plan_id'           => $plan->id,
                    'price'             => $get_amount == null ? 0 : $get_amount,
                    'price_currency'    => $currency,
                    'txn_id'            => '',
                    'payment_type'      => __('Midtrans'),
                    'payment_status'    => 'pending',
                    'receipt'           => null,
                    'user_id'           => $authuser->id,
                ]
            );
            $data = [
                'snap_token'        => $snapToken,
                'midtrans_secret'   => $midtrans_secret,
                'order_id'          => $orderID,
                'plan_id'           => $plan->id,
                'amount'            => $get_amount,
                'fallback_url'      => 'plan.get.midtrans.status'
            ];

            return view('midtrans.payment', compact('data'));
        }
        }catch (\Exception $e) {

            return redirect()->route('plan.index')->with('errors', $e->getMessage());
        }
    }

    public function planGetMidtransStatus(Request $request)
    {
        $response = json_decode($request->json, true);
        if (isset($response['status_code']) && $response['status_code'] == 200) {
            $plan = Plan::find($request['plan_id']);
            $user = auth()->user();
            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
            try {
                $Order                 = Order::where('order_id', $request['order_id'])->first();
                $Order->payment_status = 'succeeded';
                $Order->save();


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

                if ($assignPlan['is_success']) {
                    return redirect()->route('plan.index')->with('success', __('Plan activated Successfully.'));
                } else {
                    return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
                }
            } catch (\Exception $e) {
                return redirect()->route('plan.index')->with('error', __($e->getMessage()));
            }
        } else {
            return redirect()->back()->with('error', $response['status_message']);
        }
    }

    public function invoicePayWithMidtrans(Request $request)
    {
        $invoice_id = $request->invoice_id;
        $invoice    = Invoice::find($invoice_id);
        $getAmount  = $request->amount;

        if (Auth::check()) {
            $user = Auth::user();
        } else {
            $user = User::where('id', $invoice->created_by)->first();
        }

        $payment_setting    = Utility::getCompanyPaymentSetting($user->id);
        $midtrans_secret    = $payment_setting['midtrans_secret'];
        $midtrans_mode = $payment_setting['midtrans_mode'];
        $currency_code      = !empty($payment_setting['site_currency_symbol']) ? $payment_setting['site_currency_symbol'] : 'IDR';
        $get_amount         = round($request->amount);
        $orderID            = strtoupper(str_replace('.', '', uniqid('', true)));

        try {
            if ($invoice) {

                 // Set your Merchant Server Key
                \Midtrans\Config::$serverKey = $midtrans_secret;
                if($midtrans_mode == 'sandbox')
                {
                     // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
                    \Midtrans\Config::$isProduction = false;
                }
                else{
                    \Midtrans\Config::$isProduction = false;

                }
                // Set sanitization on (default)
                \Midtrans\Config::$isSanitized = true;
                // Set 3DS transaction for credit card to true
                \Midtrans\Config::$is3ds = true;

                $params = array(
                    'transaction_details' => array(
                        'order_id' => $orderID,
                        'gross_amount' => $get_amount,
                    ),
                    'customer_details' => array(
                        'first_name'    => $user->name,
                        'last_name'     => '',
                        'email'         => $user->email,
                        'phone'         => '8787878787',
                    ),
                );

                $snapToken = \Midtrans\Snap::getSnapToken($params);


                $data = [
                    'snap_token'        => $snapToken,
                    'midtrans_secret'   => $midtrans_secret,
                    'invoice_id'        => $invoice->id,
                    'amount'            => $get_amount,
                    'fallback_url'      => 'invoice.midtrans.status'
                ];

                return view('midtrans.payment', compact('data'));
            } else {
                return redirect()->back()->with('error', 'Invoice not found.');
            }
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', __($e));
        }
    }
    public function getInvociePaymentStatus(Request $request)
    {
        $orderID   = strtoupper(str_replace('.', '', uniqid('', true)));
        $invoice = Invoice::find($request->invoice_id);
        $invoice_id = $invoice->id;
        if (Auth::check()) {
            $user = Auth::user();
        } else {
            $user = User::where('id', $invoice->created_by)->first();
        }
        $settings  = Utility::settings();

        $response = json_decode($request->json, true);

        if ($invoice) {
            try {
                if (isset($response['status_code']) && $response['status_code'] == 200) {

                    try {
                        $payments               = New InvoicePayment;
                        $payments->transaction  = $orderID;
                        $payments->invoice      = $invoice->id;
                        $payments->amount       = $request->has('amount') ? $request->amount : 0;
                        $payments->date         = date('Y-m-d');
                        $payments->payment_method = 1;
                        $payments->payment_type = __('Midtrans');
                        $payments->notes        = __('Invoice') . ' ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id);
                        $payments->receipt      = '';
                        $payments->created_by   = $user->creatorId();
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

                }else{
                    return redirect()->back()->with('error', $response['status_message']);
                }
            } catch (\Exception $e) {
                if (Auth::check()) {
                    return redirect()->route('invoice.show', \Crypt::encrypt($invoice_id))->with('error', $e->getMessage());
                } else {
                    return redirect()->route('pay.invoice', \Crypt::encrypt($invoice_id))->with('error', $e->getMessage());
                }
            }
        } else {
            if (Auth::check()) {
                return redirect()->route('invoice.show', \Crypt::encrypt($invoice_id))->with('error', __('Invoice not found.'));
            } else {
                return redirect()->route('pay.invoice', \Crypt::encrypt($invoice_id))->with('error', __('Invoice not found.'));
            }
        }
    }

}

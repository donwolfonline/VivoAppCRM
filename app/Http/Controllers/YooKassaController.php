<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\User;
use App\Models\UserCoupon;
use App\Models\Utility;
use YooKassa\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Plan;
use App\Models\Order;
use App\Models\transactions;
use Illuminate\Support\Facades\DB;

class YooKassaController extends Controller
{

    public function planPayWithYooKassa(Request $request)
    {
        $payment_setting = Utility::getAdminPaymentSetting();
        $yookassa_shop_id = $payment_setting['yookassa_shop_id'];
        $yookassa_secret_key = $payment_setting['yookassa_secret_key'];
        $currency = Utility::getAdminCurrency();

        $planID = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $authuser = Auth::user();
        $plan = Plan::find($planID);
        if ($plan) {

            $get_amount = $plan->price;
            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

            if (!empty($request->coupon)) {
                $coupons = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                if (!empty($coupons)) {
                    $usedCoupun = $coupons->used_coupon();
                    $discount_value = ($plan->price / 100) * $coupons->discount;
                    $get_amount = $plan->price - $discount_value;
                    $userCoupon = new UserCoupon();
                    $userCoupon->user = $authuser->id;
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

                    // $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
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

            try {

                if (is_int((int)$yookassa_shop_id)) {
                    $client = new Client();
                    $client->setAuth((int)$yookassa_shop_id, $yookassa_secret_key);
                    // $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                    $product = !empty($plan->name) ? $plan->name : 'Life time';
                    $payment = $client->createPayment(
                        array(
                            'amount' => array(
                                'value' => $get_amount,
                                'currency' => $currency,
                            ),
                            'confirmation' => array(
                                'type' => 'redirect',
                                'return_url' => route('plan.get.yookassa.status', [$plan->id, 'order_id' => $orderID, 'price' => $get_amount]),
                            ),
                            'capture' => true,
                            'description' => 'Заказ №1',
                        ),
                        uniqid('', true)
                    );

                    $authuser = Auth::user();
                    $authuser->plan = $plan->id;
                    $authuser->save();


                    if (!empty($authuser->payment_subscription_id) && $authuser->payment_subscription_id != '') {
                        try {
                            $authuser->cancel_subscription($authuser->id);
                        } catch (\Exception $e) {
                            Log::debug($e->getMessage());
                            return redirect()->back()->with('error', $e->getMessage());

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
                            'payment_type' => __('YooKassa'),
                            'payment_status' => 'pending',
                            'receipt' => null,
                            'user_id' => $authuser->id,
                        ]
                    );


                    Session::put('payment_id', $payment['id']);

                    if ($payment['confirmation']['confirmation_url'] != null) {
                        return redirect($payment['confirmation']['confirmation_url']);
                    } else {
                        return redirect()->route('plan.index')->with('error', 'Something went wrong, Please try again');
                    }

                    // return redirect()->route('plans.index')->with('success', __('Plan Successfully Activated'));

                } else {
                    return redirect()->back()->with('error', 'Please Enter  Valid Shop Id Key');
                }
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }
    }

    public function planGetYooKassaStatus(Request $request, $planId)
    {

        $payment_setting = Utility::getAdminPaymentSetting();
        $yookassa_shop_id = $payment_setting['yookassa_shop_id'];
        $yookassa_secret_key = $payment_setting['yookassa_secret_key'];
        $currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'USD';

        if (is_int((int)$yookassa_shop_id)) {
            $client = new Client();
            $client->setAuth((int)$yookassa_shop_id, $yookassa_secret_key);
            $paymentId = Session::get('payment_id');
            Session::forget('payment_id');
            if ($paymentId == null) {
                return redirect()->back()->with('error', __('Transaction Unsuccesfull'));
            }

            $payment = $client->getPaymentInfo($paymentId);

            if (isset($payment) && $payment->status == "succeeded") {

                $plan = Plan::find($planId);
                $user = auth()->user();
                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                try {
                    $Order                 = Order::where('order_id', $request->order_id)->first();
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
                return redirect()->back()->with('error', 'Please Enter  Valid Shop Id Key');
            }
        }
    }


    public function invoicePayWithYookassa(Request $request)
    {

        $invoice_id = $request->invoice_id;
        $invoice = Invoice::find($invoice_id);
        $payment_setting = Utility::getCompanyPaymentSetting($invoice->created_by);
        $yookassa_shop_id = $payment_setting['yookassa_shop_id'];
        $yookassa_secret_key = $payment_setting['yookassa_secret_key'];
        $currency_code = isset($payment_setting['currency_code']) ? $payment_setting['currency_code'] : 'RUB';

        if (Auth::check()) {
            $user = Auth::user();
        } else {
            $user = User::where('id', $invoice->created_by)->first();
        }

        $get_amount = $request->amount;
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

        try {
            if ($invoice && $get_amount != 0) {

                if ($get_amount > $invoice->getDue())
                {
                    return redirect()->back()->with('error', __('Invalid amount.'));
                }
                else{

                if (is_int((int)$yookassa_shop_id)) {
                    $client = new Client();
                    $client->setAuth((int)$yookassa_shop_id, $yookassa_secret_key);
                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                    $payment = $client->createPayment(
                        array(
                            'amount' => array(
                                'value' => $get_amount,
                                'currency' => $currency_code ,
                            ),
                            'confirmation' => array(
                                'type' => 'redirect',
                                'return_url' => route('invoice.yookassa.status', [
                                    'invoice_id'=>$invoice->id,
                                    'amount'=>$get_amount
                                ]),
                            ),
                            'capture' => true,
                            'description' => 'Invoice Payment',
                        ),
                        uniqid('', true)
                    );

                    Session::put('invoice_payment_id', $payment['id']);

                    if ($payment['confirmation']['confirmation_url'] != null) {
                        return redirect($payment['confirmation']['confirmation_url']);
                    } else {
                        return redirect()->back()->with('error', 'Something went wrong, Please try again');
                    }


                } else {
                    return redirect()->back()->with('error', 'Please Enter  Valid Shop Id Key');
                }
            }
            } else {
                return redirect()->back()->with('error', 'Invoice not found.');
            }
        } catch (\Throwable $e) {

            return redirect()->back()->with('error', __($e));
        }
    }
    public function getInvociePaymentStatus(Request $request,$invoice_id)
    {
        $invoice = Invoice::find($invoice_id);
        $payment_setting = Utility::getCompanyPaymentSetting($invoice->created_by);
        $yookassa_shop_id = $payment_setting['yookassa_shop_id'];
        $yookassa_secret_key = $payment_setting['yookassa_secret_key'];
        $get_amount = $request->amount;

        // $invoice = Invoice::find($request->invoice_id);
        $orderID  = strtoupper(str_replace('.', '', uniqid('', true)));
        $settings  = Utility::settings();
        if(Auth::check()){
            $user = Auth::user();
        }
        else{

            $user=User::where('id',$invoice->created_by)->first();
        }

        if ($invoice) {
            try {
                if (is_int((int)$yookassa_shop_id)) {
                    $client = new Client();
                    $client->setAuth((int)$yookassa_shop_id, $yookassa_secret_key);
                    $paymentId = Session::get('invoice_payment_id');

                    if ($paymentId == null) {
                        return redirect()->back()->with('error', __('Transaction Unsuccesfull'));
                    }
                    $payment = $client->getPaymentInfo($paymentId);

                    Session::forget('invoice_payment_id');

                    if (isset($payment) && $payment->status == "succeeded") {

                            try {

                                $payments = New InvoicePayment;
                                $payments->transaction = $orderID;
                                $payments->invoice = $invoice->id;
                                $payments->amount = $get_amount;
                                $payments->date = date('Y-m-d');
                                $payments->payment_method = 1;
                                $payments->payment_type = __('Yookassa');
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

                        } catch (\Exception $e)
                        {
                            return redirect()->route('invoice.show', \Crypt::encrypt($invoice_id))->with('error', __($e->getMessage()));
                        }
                    } else {
                        return redirect()->back()->with('error', 'Please Enter  Valid Shop Id Key');
                    }
                }
            } catch (\Exception $e) {
                if (Auth::check()) {
                    return redirect()->route('invoice.show', \Crypt::encrypt($invoice_id))->with('error', $e->getMessage());
                } else {
                    return redirect()->route('pay.invoice', \Crypt::encrypt($invoice_id))->with('success', $e->getMessage());
                }
            }
        } else {
            if (Auth::check()) {
                return redirect()->route('invoice.show', \Crypt::encrypt($invoice_id))->with('error', __('Invoice not found.'));
            } else {
                return redirect()->route('pay.invoice', \Crypt::encrypt($invoice_id))->with('success', __('Invoice not found.'));
            }
        }
    }

}

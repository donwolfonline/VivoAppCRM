<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\User;
use App\Models\Order;
use Modules\PaiementPro\Events\PaiementProPaymentStatus;
use Illuminate\Support\Facades\Session;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use App\Models\transactions;
use App\Models\UserCoupon;
use App\Models\Utility;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class PaiementProController extends Controller
{

    public function planPayWithpaiementpro(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'mobileno' => 'required|numeric|min:10',
                'Channle' => 'required|in:OMCIV2,MOMO,CARD,FLOOZ,PAYPAL'

            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }
        // dd($request);

        $payment_setting = Utility::getAdminPaymentSetting();
        // dd($payment_setting);
        $merchant_id = $payment_setting['PaiementPro_merchant_key'];
        $currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'USD';
        $admin_settings = DB::table("admin_payment_settings")->get();
        $planID = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $plan = Plan::find($planID);
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        $user = Auth::user();
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
            $response = ['orderId' => $orderID, 'user' => $user, 'get_amount' => $get_amount, 'plan' => $plan, 'currency' => $currency, 'coupon_id' => $request->coupon];
            $merchant_id = isset($payment_setting['PaiementPro_merchant_key']) ? $payment_setting['PaiementPro_merchant_key'] : '';

            $call_back = route('plan.get.paiementpro.status', [
                $plan->id,
            ]);
            // dd($admin_settings);
            $data = array(
                'merchantId' => $merchant_id,
                'amount' =>  $get_amount,
                'description' => "Api PHP",
                'channel' => $request->channel,
                'countryCurrencyCode' => !empty($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '',
                'referenceNumber' => "REF-" . time(),
                'customerEmail' => $user->email,
                'customerFirstName' => $user->name,
                'customerLastname' =>  $user->name,
                'customerPhoneNumber' => $request->mobileno,
                'notificationURL' => $call_back,
                'returnURL' => $call_back,
                'returnContext' => json_encode(['coupon_code' => $request->coupon_code]),
            );
            // dd($data , $request->mobile_number);

            $data = json_encode($data);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://www.paiementpro.net/webservice/onlinepayment/init/curl-init.php");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $response = curl_exec($ch);

            curl_close($ch);
            $response = json_decode($response);
            // dd($response);


            // $response = json_decode($response);
            if (isset($response->success) && $response->success == true) {
                // dd($response);
                return redirect($response->url);

                return redirect()
                    ->route('plan.index', \Illuminate\Support\Facades\Crypt::encrypt($plan->id))
                    ->with('error', 'Something went wrong. OR Unknown error occurred');
            } else {
                return redirect()
                    ->route('plan.index', \Illuminate\Support\Facades\Crypt::encrypt($plan->id))
                    ->with('error', $response->message ?? 'Something went wrong.');
            }
        }


    public function planGetpaiementproStatus(Request $request, $plan_id)
    {
        $admin_settings = Utility::getAdminPaymentSetting();
        $data = request()->all();
        $fixedData = [];
        foreach ($data as $key => $value) {
            $fixedKey = str_replace('amp;', '', $key);
            $fixedData[$fixedKey] = $value;
        }

        $authuser = Auth::user();
        $plan = Plan::find($plan_id);
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
                'price' => !empty($request->amount) ? $request->amount : 0,
                'price_currency' => isset($admin_settings['defult_currancy']) ? $admin_settings['defult_currancy'] : '',
                'txn_id' => '',
                'payment_type' => __('Paiement Pro'),
                'payment_status' => 'succeeded',
                'receipt' => null,
                'user_id' => $authuser->id,
            ]
        );

        $assignPlan = $authuser->assignPlan($plan->id);

        if (!empty($fixedData['coupon_id'])) {
            if (!empty($coupons)) {
                $userCoupon = new UserCoupon();

                    $userCoupon->user = Auth::user()->id;
                    $userCoupon->coupon = $coupons->id;
                    $userCoupon->order= $orderID;
                    $userCoupon->save();
                $usedCoupun = $coupons->used_coupon();
                if ($coupons->limit <= $usedCoupun) {
                    $coupons->is_active = 0;
                    $coupons->save();
                }
            }
        }


        if ($assignPlan['is_success']) {
            $user = Auth::user();
            $referral = DB::table('referral_programs')->first();

            if($referral != null && isset($referral->commission)){


            $amount =  ($plan->price * $referral->commission) / 100;

            $referral = DB::table('referral_programs')->first();
            $transactions = transactions::where('uid', $user->id)->get();
            $total = count($transactions);

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
        }

            return redirect()->route('plan.index')->with('success', __('Plan activated Successfully!'));
        } else {
            return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
        }
    }

    public function invoicePayWithpaiementpro(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'mobile' => 'required|numeric|min:10',
                'Channle' => 'required|in:OMCIV2,MOMO,CARD,FLOOZ,PAYPAL'

            ]
        );
        // dd($validator);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $invoiceID = $request->invoice_id;
        $invoiceID = \Crypt::decrypt($invoiceID);
        $invoice   = Invoice::find($invoiceID);

        $user = User::where('id', $invoice->created_by)->first();
        $payment_setting = Utility::invoice_payment_settings($invoice->created_by);
        $get_amount = $request->amount;
        $type = 'invoice';
        $merchant_id = isset($payment_setting['PaiementPro_merchant_key']) ? $payment_setting['PaiementPro_merchant_key'] : '';


        if ($invoice) {
            // $this->paymentConfig($invoice->created_by,$invoice->workspace);
            if ($get_amount > $invoice->getDue()) {
                return redirect()->back()->with('error', __('Invalid amount.'));
            } else {
                $call_back = route('invoice.paiementpro', [$invoiceID, $type, $get_amount]);
                $data = array(
                    'merchantId' => $merchant_id,
                    'amount' =>  $get_amount,
                    'description' => "Api PHP",
                    'channel' =>  $request->channel,
                    'countryCurrencyCode' => !empty($payment_setting['currency_symbol']) ? $payment_setting['currency_symbol'] : '',
                    'referenceNumber' => "REF-" . time(),
                    'customerEmail' => $user->email,
                    'customerFirstName' => $user->name,
                    'customerLastname' =>  $user->name,
                    'customerPhoneNumber' => $request->mobile,
                    'notificationURL' => $call_back,
                    'returnURL' => $call_back,
                    'returnContext' => "",
                );

                $data = json_encode($data);


                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://www.paiementpro.net/webservice/onlinepayment/init/curl-init.php");
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HEADER, FALSE);
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

                $response = curl_exec($ch);

                curl_close($ch);
                $response = json_decode($response);
                if (isset($response->success) && $response->success == true) {
                    // redirect to approve href
                    return redirect($response->url);

                    return redirect()->back()->with('error', 'Something went wrong.');
                } else {
                    if (\Auth::user()) {
                        if ($request->type == 'invoice') {
                            return redirect()->route('invoice.show', $invoiceID)->with('error', $response->message ?? 'Something went wrong.');
                        } elseif ($request->type == 'salesinvoice') {
                            return redirect()->route('salesinvoice.show', $invoiceID)->with('error', $response->message ?? 'Something went wrong.');
                        } elseif ($request->type == 'retainer') {
                            return redirect()->route('retainer.show', $invoiceID)->with('error', $response->message ?? 'Something went wrong.');
                        }
                    }
                }

                return redirect()->back()->with('error', __($response->message ?? 'Unknown error occurred'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getInvoicePaymentStatus(Request $request, $invoice_id, $type, $get_amount)
    {

        if (!empty($invoice_id)) {
            $invoice    = \App\Models\Invoice::find($invoice_id);
            // $this->paymentConfig($invoice->created_by,$invoice->workspace);
            $payment_setting = Utility::invoice_payment_settings($invoice->created_by);


            if ($type == 'invoice') {

                $invoice_id = $invoice_id;
                $invoice    = \App\Models\Invoice::find($invoice_id);
                if (Auth::check()) {
                    $user = \Auth::user();
                } else {
                    $user = User::where('id', $invoice->created_by)->first();
                }
                $settings  = DB::table('settings')->where('created_by', '=', $user->creatorId())->get()->pluck('value', 'name');
                if ($invoice) {
                    try {

                        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                        // dd($request->responsecode == "-1");

                        if ($request->responsecode == "0") {
                            $invoice_payment                 = new InvoicePayment();
                            $invoice_payment->transaction = $orderID;
                            $invoice_payment->invoice     = $invoice_id;
                            $invoice_payment->amount         = $request->amount;
                            $invoice_payment->date           = date('Y-m-d');
                            $invoice_payment->payment_method =  __('Paiement Pro');
                            $invoice_payment->payment_type   = 'Paiement Pro';
                            $invoice_payment->notes = __('Invoice') . ' ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id);
                            $invoice_payment->receipt = '';
                            $invoice_payment->created_by = $user->creatorId();

                            $invoice_payment->save();

                            $invoice = Invoice::find($invoice->id);

                            if ($invoice->getDue() <= 0.0) {
                                Invoice::change_status($invoice->id, 5);
                            } elseif ($invoice->getDue() > 0) {
                                Invoice::change_status($invoice->id, 4);
                            } else {
                                Invoice::change_status($invoice->id, 3);
                            }

                            if (\Auth::check()) {
                                $user = Auth::user();
                            } else {
                                $user = User::where('id', $invoice->created_by)->first();
                            }

                            $settings  = Utility::settings();
                            // if(isset($settings['payment_create_notification']) && $settings['payment_create_notification'] ==1){
                            //      $msg = __('New payment of ').$request->amount.' '.__('created for ').$user->name.__(' by Mercado Pago').'.';
                            //     Utility::send_slack_msg($msg);
                            // }
                            if (isset($settings['payment_create_notification']) && $settings['payment_create_notification'] == 1) {
                                $uArr = [
                                    'user_name' => $user->name,
                                    'amount' => $request->amount,
                                    'created_by' => 'by Mercado Pago',
                                ];
                                Utility::send_slack_msg('new_payment', $uArr);
                            }
                            if (isset($settings['telegram_payment_create_notification']) && $settings['telegram_payment_create_notification'] == 1) {
                                $uArr = [
                                    'user_name' => $user->name,
                                    'amount' => $request->amount,
                                    'created_by' => 'by Mercado Pago',
                                ];
                                Utility::send_telegram_msg('new_payment', $uArr);
                            }
                            if (isset($settings['twilio_invoice_payment_create_notification']) && $settings['twilio_invoice_payment_create_notification'] == 1) {
                                $uArr = [
                                    'user_name' => $user->name,
                                    'amount' => $request->amount,
                                    'created_by' => 'by Mercado Pago',
                                ];
                                Utility::send_twilio_msg('new_payment', $uArr);
                            }
                            $module = 'Invoice Status Update';
                            $webhook =  Utility::webhookSetting($module, $invoice->created_by);
                            if ($webhook) {
                                $parameter = json_encode($invoice);
                                // 1 parameter is  URL , 2 parameter is data , 3 parameter is method
                                $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                                // dd($status);
                                if ($status == true) {
                                    return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Invoice paid Successfully!'));
                                } else {
                                    return redirect()->back()->with('error', __('Webhook call failed.'));
                                }
                            }


                            return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Invoice paid Successfully!'));
                        } else {


                            return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Transaction fail'));
                        }
                    } catch (\Exception $e) {

                        return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', $e->getMessage());
                    }
                } else {

                    return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Invoice not found.'));
                }
            }
        } else {
            if (\Auth::check()) {
                return redirect()->back()->with('error', __('Invoice not found.'));
            } else {
                return redirect()->back()->with('error', __('Invoice not found.'));
            }
        }
    }
}

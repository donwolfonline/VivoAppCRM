<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Deal;
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
use Google\Service\Calendar\Channel;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

use function Laravel\Prompts\error;
use function PHPSTORM_META\type;

class CinetPayController extends Controller
{
    public function planPayWithcinetpay(Request $request)
    {
        $payment_setting = Utility::getAdminPaymentSetting();

        $merchant_id = isset($payment_setting['CinetPay_api_key']) ? $payment_setting['CinetPay_api_key'] : '';
        $site_id = isset($payment_setting['CinetPay_site_id']) ? $payment_setting['CinetPay_site_id'] : '';

        $currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'USD';
        $planID = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);

        $plan = Plan::find($planID);
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        $user = Auth::user();
try{


        if ($plan) {

            $get_amount = $plan->price;
            if (!empty($request->coupon)) {
                $coupons = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                if (!empty($coupons)) {
                    $usedCoupon = $coupons->used_coupon();
                    $discount_value = ($plan->price / 100) * $coupons->discount;
                    $get_amount = $plan->price - $discount_value;
                    if ($coupons->limit == $usedCoupon) {
                        return redirect()->back()->with('error', __('This coupon code has expired.'));
                    }
                    $coupon_id = $coupons->id;
                    if ($get_amount < 1) {
                        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                        $statuses = 'success';
                        if ($coupon_id != '') {

                            $userCoupon         = new UserCoupon(); //UsersCoupons
                            $userCoupon->user_id   = $user->id;
                            $userCoupon->coupon_id = $coupons->id;
                            $userCoupon->order_id  = $orderID;
                            $userCoupon->save();
                            $usedCoupon = $coupons->used_coupon();
                            if ($coupons->limit <= $usedCoupon) {
                                $coupons->is_active = 0;
                                $coupons->save();
                            }
                        }
                        //$user = Auth::user();
                        $order                 = new Order();
                        $order->order_id       = $orderID;
                        $order->name           = $user->name;
                        $order->card_number    = '';
                        $order->card_exp_month = '';
                        $order->card_exp_year  = '';
                        $order->plan_name      = $plan->name;
                        $order->plan_id        = $plan->id;
                        $order->price          =  $get_amount;
                        $order->price_currency = $currency;
                        $order->txn_id         = '';
                        $order->payment_type   = __('CinetPay');
                        $order->payment_status  = $statuses;
                        $order->receipt        = '';
                        $order->user_id        = $user->id;

                        $order->save();

                        $assignPlan = $user->assignPlan($plan->id);

                        if ($assignPlan['is_success']) {
                            return redirect()->route('plans.index')->with('success', __('Plan activated Successfully.'));
                        } else {

                            return redirect()->route('plans.index')->with('error', __($assignPlan['error']));
                        }
                    }
                } else {

                    return redirect()->back()->with('error', __('This coupon code is invalid or has expired.'));
                }
            }


            $call_back = route('plan.cinetpay.status', [
                $plan->id,
            ], '?_token=' . csrf_token());

            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
            $cinetpay_data =  [
                "amount" => $get_amount,
                "currency" => $currency,
                "apikey" =>  $merchant_id,
                "site_id" =>  $site_id,
                // "secret_key"=>  $secret_key,
                "transaction_id" => $orderID,
                "description" => "TEST-Laravel",
                // "return_url" => $call_back,
                // "return_url" => route('plan.cinetpay.status', [$plan->id]). '?_token=' . csrf_token(),
                "return_url" => route('plan.cinetpay.status') . '?_token=' . csrf_token(),
                "metadata" => "user001",
                'customer_surname' => "test",
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone_number' => '9658745214',
                'customer_address' => 'abu dhabi',
                'customer_city' => 'texas',
                'customer_country' => 'BF',
                'customer_state' => 'USA',
                'customer_zip_code' => '123456',
                'invoice_data' => [
                    'coupon_id' => $request->coupon,
                    'amount' => $get_amount,
                    'plan_id' => $plan->id,
                ]
            ];

            // dd($cinetpay_data);

            $curl = curl_init();

            $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://api-checkout.cinetpay.com/v2/payment',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 45,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($cinetpay_data),
                    CURLOPT_SSL_VERIFYPEER => 0,
                    CURLOPT_HTTPHEADER => array(
                        "content-type:application/json"
                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
                // dd($response);
                $response_body = json_decode($response, true);

                if ($response_body['code'] == '201') {
                    $payment_link = $response_body["data"]["payment_url"];
                    return redirect($payment_link);
                }
                else {
                    return redirect()->back()->with('errors', 'Something went wrong.');
                }
            }

            } catch (\Throwable $e) {
                return redirect()->back()->with('error', __($e->getMessage()));
            }

            }



            public function planGetCinetpayStatus(Request $request)
            {

                $data = request()->all();
                $authuser = Auth::user();

                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                $payment_setting = Utility::getAdminPaymentSetting();
                $api_key = isset($payment_setting['cinetpay_api_key']) ? $payment_setting['cinetpay_api_key'] : '';
                $site_id = isset($payment_settting['cinetpay_site_id']) ? $payment_setting['cinetpay_site_id'] : '';
                $currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'USD';
                $cinetpay_check = [
                    "apikey" => $api_key,
                    "site_id" => $site_id,
                    "transaction_id" => $orderID
                ];

                $response = $this->getPayStatus($cinetpay_check);

                $response_body = json_decode($response, true);
                if ($response_body['code'] == '00') {
                    $plan = Plan::find($request->plan_id);
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
                            'payment_type' => __('Cinetpay'),
                            'payment_status' => 'succeeded',
                            'receipt' => null,
                            'user_id' => $authuser->id,
                        ]
                    );

                    $assignPlan = $authuser->assignPlan($plan->id);

                    $coupons = Coupon::where('code', $request->coupon_id)->first();
                    if (!empty($request->coupon_id)) {
                        if (!empty($coupons)) {
                            $userCoupon = new UserCoupon();
                            $userCoupon->user_id = $authuser->id;
                            $userCoupon->coupon_id = $coupons->id;
                            $userCoupon->order_id = $orderID;
                            $userCoupon->save();
                            $usedCoupon = $coupons->used_coupon();
                            if ($coupons->limit <= $usedCoupon) {
                                $coupons->is_active = 0;
                                $coupons->save();
                            }
                        }
                    }


                    if ($assignPlan['is_success']) {
                        Utility::referraltransaction($plan);
                        return redirect()->route('plans.index')->with('success', __('Plan activated Successfully!'));
                    } else {
                        return redirect()->route('plans.index')->with('error', __($assignPlan['error']));
                    }
                } else {
                    return redirect()->back()->with('error', 'Transaction has been failed.');
                }
            }
    public function getPayStatus($data)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api-checkout.cinetpay.com/v2/payment/check',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTPHEADER => array(
                "content-type:application/json"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err)
            print($err);
        else
            return ($response);
    }


    public function invoicePayWithpaiementpro(Request $request)
    {
        $invoiceID = $request->invoice_id;
        $invoiceID = \Crypt::decrypt($invoiceID);
        $invoice   = Invoice::find($invoiceID);

        $user = User::where('id', $invoice->created_by)->first();
        $payment_setting = Utility::invoice_payment_settings($invoice->created_by);
        $get_amount = $request->amount;
        $type = 'invoice';
        $merchant_id = isset($payment_setting['CinetPay_api_key']) ? $payment_setting['CinetPay_api_key'] : '';
        $site_id = isset($payment_setting['CinetPay_site_id']) ? $payment_setting['CinetPay_site_id'] : '';
        $currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'USD';

        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

        if ($invoice) {
            if ($get_amount > $invoice->getDue()) {
                return redirect()->back()->with('error', __('Invalid amount.'));
            } else {
                $call_back = route('invoice.paiementpro', [$invoiceID, $type, $get_amount]);
                $token= csrf_token();

                $data =  [
                    "amount"=> $get_amount,
                    "currency"=> 'XOF',
                    "apikey"=> $merchant_id,
                    "site_id"=>$site_id,
                    "transaction_id"=> $orderID,
                    "description"=> "TEST-Laravel",
                    "return_url" => route('customer.status.with.CinetPay',$token),
                    "notify_url" => route('notify_url'),
                    "metadata"=> "user001",
                    'customer_surname'=> "test",
                    'customer_name' => $user->name,
                    'customer_email' => $user->email,
                    'customer_phone_number'=> '9658745214',
                    'customer_address'=> 'abu dhabi',
                    'customer_city'=> 'texas',
                    'customer_country'=> 'BF' ,
                    'customer_state'=> 'USA',
                    'customer_zip_code'=> '123456',
                    'invoice_data' => [
                        'coupon_id' => $request->coupon,
                        'amount' => $get_amount,
                        'invoice' => $invoice->id,
                    ]
                ];

                $ch = curl_init();

                curl_setopt_array($ch, array(
                    CURLOPT_URL => 'https://api-checkout.cinetpay.com/v2/payment',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 45,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($data),
                    CURLOPT_SSL_VERIFYPEER => 0,
                    CURLOPT_HTTPHEADER => array(
                        "content-type:application/json"
                    ),
                ));

                $response = curl_exec($ch);
                curl_close($ch);
                $response = json_decode($response);
                // dd($response);

                if (isset($response->code) && $response->code == "201") {
                    // Transaction created successfully, redirect to the provided URL
                    return redirect($response->data->payment_url);

                } else {
                    // Error occurred, handle accordingly
                    return redirect()->back()->with('error', $response->message ?? 'Unknown error occurred');
                }
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    // public function getInvoicePaymentStatus(Request $request, $invoice_id, $type, $get_amount)
    // {

    //     if (!empty($invoice_id)) {
    //         $invoice    = \App\Models\Invoice::find($invoice_id);
    //         // $this->paymentConfig($invoice->created_by,$invoice->workspace);
    //         $payment_setting = Utility::invoice_payment_settings($invoice->created_by);


    //         if ($type == 'invoice') {

    //             $invoice_id = $invoice_id;
    //             $invoice    = \App\Models\Invoice::find($invoice_id);
    //             if (Auth::check()) {
    //                 $user = \Auth::user();
    //             } else {
    //                 $user = User::where('id', $invoice->created_by)->first();
    //             }
    //             $settings  = DB::table('settings')->where('created_by', '=', $user->creatorId())->get()->pluck('value', 'name');
    //             if ($invoice) {
    //                 try {

    //                     $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
    //                     // dd($request->responsecode == "-1");

    //                     if ($request->responsecode == "0") {
    //                         $invoice_payment                 = new InvoicePayment();
    //                         $invoice_payment->transaction = $orderID;
    //                         $invoice_payment->invoice     = $invoice_id;
    //                         $invoice_payment->amount         = $request->amount;
    //                         $invoice_payment->date           = date('Y-m-d');
    //                         $invoice_payment->payment_method =  __('Paiement Pro');
    //                         $invoice_payment->payment_type   = 'Paiement Pro';
    //                         $invoice_payment->notes = __('Invoice') . ' ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id);
    //                         $invoice_payment->receipt = '';
    //                         $invoice_payment->created_by = $user->creatorId();

    //                         $invoice_payment->save();

    //                         $invoice = Invoice::find($invoice->id);

    //                         if ($invoice->getDue() <= 0.0) {
    //                             Invoice::change_status($invoice->id, 5);
    //                         } elseif ($invoice->getDue() > 0) {
    //                             Invoice::change_status($invoice->id, 4);
    //                         } else {
    //                             Invoice::change_status($invoice->id, 3);
    //                         }

    //                         if (\Auth::check()) {
    //                             $user = Auth::user();
    //                         } else {
    //                             $user = User::where('id', $invoice->created_by)->first();
    //                         }

    //                         $settings  = Utility::settings();
    //                         // if(isset($settings['payment_create_notification']) && $settings['payment_create_notification'] ==1){
    //                         //      $msg = __('New payment of ').$request->amount.' '.__('created for ').$user->name.__(' by Mercado Pago').'.';
    //                         //     Utility::send_slack_msg($msg);
    //                         // }
    //                         if (isset($settings['payment_create_notification']) && $settings['payment_create_notification'] == 1) {
    //                             $uArr = [
    //                                 'user_name' => $user->name,
    //                                 'amount' => $request->amount,
    //                                 'created_by' => 'by Mercado Pago',
    //                             ];
    //                             Utility::send_slack_msg('new_payment', $uArr);
    //                         }
    //                         if (isset($settings['telegram_payment_create_notification']) && $settings['telegram_payment_create_notification'] == 1) {
    //                             $uArr = [
    //                                 'user_name' => $user->name,
    //                                 'amount' => $request->amount,
    //                                 'created_by' => 'by Mercado Pago',
    //                             ];
    //                             Utility::send_telegram_msg('new_payment', $uArr);
    //                         }
    //                         if (isset($settings['twilio_invoice_payment_create_notification']) && $settings['twilio_invoice_payment_create_notification'] == 1) {
    //                             $uArr = [
    //                                 'user_name' => $user->name,
    //                                 'amount' => $request->amount,
    //                                 'created_by' => 'by Mercado Pago',
    //                             ];
    //                             Utility::send_twilio_msg('new_payment', $uArr);
    //                         }
    //                         $module = 'Invoice Status Update';
    //                         $webhook =  Utility::webhookSetting($module, $invoice->created_by);
    //                         if ($webhook) {
    //                             $parameter = json_encode($invoice);
    //                             // 1 parameter is  URL , 2 parameter is data , 3 parameter is method
    //                             $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
    //                             // dd($status);
    //                             if ($status == true) {
    //                                 return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Invoice paid Successfully!'));
    //                             } else {
    //                                 return redirect()->back()->with('error', __('Webhook call failed.'));
    //                             }
    //                         }


    //                         return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Invoice paid Successfully!'));
    //                     } else {

    //                         return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Transaction fail'));
    //                     }
    //                 } catch (\Exception $e) {

    //                     return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', $e->getMessage());
    //                 }
    //             } else {

    //                 return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Invoice not found.'));
    //             }
    //         }
    //     } else {
    //         if (\Auth::check()) {
    //             return redirect()->back()->with('error', __('Invoice not found.'));
    //         } else {
    //             return redirect()->back()->with('error', __('Invoice not found.'));
    //         }
    //     }
    // }




    // public function getInvoicePaymentStatus(Request $request, $invoice_id, $type, $get_amount)
    // {
    //     $curl = curl_init();

    //     curl_setopt_array($curl, array(
    //         CURLOPT_URL => 'https://api-checkout.cinetpay.com/v2/payment/check',
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => "",
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 45,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => 'POST',
    //         CURLOPT_POSTFIELDS => json_encode($data),
    //         CURLOPT_SSL_VERIFYPEER => 0,
    //         CURLOPT_HTTPHEADER => array(
    //             "content-type:application/json",
    //         ),
    //     ));
    //     $response = curl_exec($curl);
    //     $err = curl_error($curl);
    //     curl_close($curl);
    //     if ($err) {
    //         return redirect()->back()->with('error', __('Something went wrong!'));
    //     } else {
    //         return ($response);
    //     }

    // }

    public function getInvoicePaymentStatus($data)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api-checkout.cinetpay.com/v2/payment/check',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTPHEADER => array(
                "content-type:application/json",
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return redirect()->back()->with('error', __('Something went wrong!'));
        } else {
            return redirect()->back()->with('error', __('Payment Fail'));
        }

    }
}


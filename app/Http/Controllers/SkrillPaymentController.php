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
use Obydul\LaraSkrill\SkrillClient;
use Obydul\LaraSkrill\SkrillRequest;

class SkrillPaymentController extends Controller
{
    public $email;
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

        $this->email      = isset($payment_setting['skrill_email']) ? $payment_setting['skrill_email'] : '';
        $this->is_enabled = isset($payment_setting['is_skrill_enabled']) ? $payment_setting['is_skrill_enabled'] : 'off';
        return $this;
    }

    public function planPayWithSkrill(Request $request)
    {
        $user=\Auth::user();
        $payment    = $this->paymentConfig($user);
        $planID     = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $plan       = Plan::find($planID);
        $authuser   = Auth::user();
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
                            'payment_type' => __('Skrill'),
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
            $tran_id             = md5(date('Y-m-d') . strtotime('Y-m-d H:i:s') . 'user_id');
            $skill               = new SkrillRequest();
            $skill->pay_to_email = $this->email;
            $skill->return_url   = route(
                'plan.skrill', [
                                 $request->plan_id,
                                 'tansaction_id=' . MD5($tran_id),
                                 'coupon_id=' . $coupons_id,
                             ]
            );
            $skill->cancel_url   = route('plan.skrill', [$request->plan_id]);

            // create object instance of SkrillRequest
            $skill->transaction_id  = MD5($tran_id); // generate transaction id
            $skill->amount          = $price;
            $skill->currency        = Utility::getAdminCurrency();
            $skill->language        = 'EN';
            $skill->prepare_only    = '1';
            $skill->merchant_fields = 'site_name, customer_email';
            $skill->site_name       = \Auth::user()->name;
            $skill->customer_email  = \Auth::user()->email;

            // create object instance of SkrillClient
            $client = new SkrillClient($skill);
            $sid    = $client->generateSID(); //return SESSION ID

            // handle error
            $jsonSID = json_decode($sid);
            if($jsonSID != null && $jsonSID->code == "BAD_REQUEST")
            {
                return redirect()->back()->with('error', $jsonSID->message);
            }


            // do the payment
            $redirectUrl = $client->paymentRedirectUrl($sid); //return redirect url

            if($tran_id)
            {
                $data = [
                    'amount' => $price,
                    'trans_id' => MD5($request['transaction_id']),
                    'currency' => Utility::getAdminCurrency(),
                    'coupon_id' => $coupons_id,
                ];
                session()->put('skrill_data', $data);
            }

            return redirect($redirectUrl);

        }
        else
        {
            return redirect()->back()->with('error', 'Plan is deleted.');
        }

    }
    public function getPaymentStatus(Request $request, $plan)
    {

        $planID  = \Illuminate\Support\Facades\Crypt::decrypt($plan);
        $plan    = Plan::find($planID);
        $user    = \Auth::user();
        $orderID = time();
        if($plan)
        {
            try
            {

                if(session()->has('skrill_data'))
                {
                    $get_data = session()->get('skrill_data');

                    if($get_data['coupon_id'] && $get_data['coupon_id'] != '')
                    {

                        $coupons = Coupon::find($get_data['coupon_id']);

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
                    $order->price          = isset($get_data['amount']) ? $get_data['amount'] : 0;
                    $order->price_currency = Utility::getAdminCurrency();
                    $order->txn_id         = isset($request->transaction_id) ? $request->transaction_id : '';
                    $order->payment_type   = __('Skrill');
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
            catch(\Exception $e)
            {
                return redirect()->route('plan.index')->with('error', __('Plan not found!'));
            }
        }
    }


    public function invoicePayWithSkrill(Request $request)
    {
        $invoiceID = $request->invoice_id;

        $invoiceID = \Crypt::decrypt($invoiceID);

        $invoice = Invoice::find($invoiceID);
        if(\Auth::check())
        {
            $user=\Auth::user();
        }
        else
        {
            $user= User::where('id',$invoice->created_by)->first();
        }

        $payment = $this->paymentConfig($user);
        // dd($request['transaction_id']);



        if($invoice)
        {

            $price = $request->amount;
            if($price > 0)
            {

                $tran_id             = md5(date('Y-m-d') . strtotime('Y-m-d H:i:s') . 'user_id');
                $skill               = new SkrillRequest();
                $skill->pay_to_email = $this->email;
                $skill->return_url   = route(
                    'invoice.skrill', [
                                        $request->invoice_id,
                                        $price,
                                        'tansaction_id=' . MD5($tran_id),
                                    ]
                );
                $skill->cancel_url   = route(
                    'invoice.skrill', [
                    $request->invoice_id,
                    $price,
                ]
                );

                // create object instance of SkrillRequest
                $skill->transaction_id  = MD5($tran_id); // generate transaction id
                $skill->amount          = $price;
                $skill->currency        = Utility::settingsById($invoice->created_by)['site_currency'];
                $skill->language        = 'EN';
                $skill->prepare_only    = '1';
                $skill->merchant_fields = 'site_name, customer_email';
                $skill->site_name       = $user->name;
                $skill->customer_email  = $user->email;

                // create object instance of SkrillClient
                $client = new SkrillClient($skill);
                $sid    = $client->generateSID(); //return SESSION ID

                // handle error
                $jsonSID = json_decode($sid);
                if($jsonSID != null && $jsonSID->code == "BAD_REQUEST")
                {
                    return redirect()->back()->with('error', $jsonSID->message);
                }


                // do the payment
                $redirectUrl = $client->paymentRedirectUrl($sid); //return redirect url
                if($tran_id)
                {
                    $data = [
                        'amount' => $price,
                        'trans_id' => MD5($request['transaction_id']),
                        'currency' => Utility::settingsById($invoice->created_by)['site_currency'],
                    ];
                    session()->put('skrill_data', $data);
                }

                return redirect($redirectUrl);

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

        $invoiceID =  \Illuminate\Support\Facades\Crypt::decrypt($invoice_id);
        $invoice   = Invoice::find($invoiceID);

        if(\Auth::check())
        {
            $user=\Auth::user();
        }
        else
        {
            $user= User::where('id',$invoice->created_by)->first();
        }

        $payment  = $this->paymentConfig($user);
        $orderID  = strtoupper(str_replace('.', '', uniqid('', true)));
        $settings = DB::table('settings')->where('created_by', '=', $user->creatorId())->get()->pluck('value', 'name');

        $invoiceID =  \Illuminate\Support\Facades\Crypt::decrypt($invoice_id);
        $invoice   = Invoice::find($invoiceID);
        $result    = array();

        if($invoice)
        {
            // try
            // {
                if(session()->has('skrill_data'))
                {
                    $get_data = session()->get('skrill_data');

                    $payments = InvoicePayment::create(
                        [
                            'invoice' => $invoice->id,
                            'date' => date('Y-m-d'),
                            'amount' => $amount,
                            'payment_method' => 1,
                            'transaction' => $orderID,
                            'payment_type' => __('Skrill'),
                            'receipt' => '',
                            'notes' => __('Invoice') . ' ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id),
                        ]
                    );

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
                    //     $msg = __('New payment of ').$amount.' '.__('created for ').$user->name.__(' by Skrill').'.';
                    //     Utility::send_slack_msg($msg);
                    // }
                    if (isset($settings['payment_create_notification']) && $settings['payment_create_notification'] == 1) {
                        $uArr = [
                            'user_name' => $user->name,
                            'amount'=> $amount,
                            'created_by'=> 'by Skrill',
                        ];
                        Utility::send_slack_msg('new_payment', $uArr);
                        }
                    if (isset($settings['telegram_payment_create_notification']) && $settings['telegram_payment_create_notification'] == 1) {
                        $uArr = [
                            'user_name' => $user->name,
                            'amount'=> $amount,
                            'created_by'=> 'by Skrill',
                        ];
                        Utility::send_telegram_msg('new_payment', $uArr);
                        }
                    if (isset($settings['twilio_invoice_payment_create_notification']) && $settings['twilio_invoice_payment_create_notification'] == 1) {
                        $uArr = [
                            'user_name' => $user->name,
                            'amount'=> $amount,
                            'created_by'=> 'by Skrill',
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

                    return redirect()->back()->with('success', __(' Payment successfully added.'));

                }
                else
                {
                    return redirect()->back()->with('error', __('Transaction has been failed! '));
                }
                // }
                // catch(\Exception $e)
                // {
                //     return redirect()->back()->with('error', __('Invoice not found!'));
                // }
        }
    }

    // public function invoicePayWithSkrill(Request $request)
    // {

    //     $invoiceID = $request->invoice_id;
    //     $invoiceID = \Crypt::decrypt($invoiceID);
    //     $invoice = Invoice::find($invoiceID);
    //     if(\Auth::check())
    //     {
    //         $user=\Auth::user();
    //     }
    //     else
    //     {
    //         $user= User::where('id',$invoice->created_by)->first();
    //     }

    //     $payment = $this->paymentConfig($user);

    //     if($invoice)
    //     {

    //         $price = $request->amount;
    //         if($price > 0)
    //         {

    //             $tran_id             = md5(date('Y-m-d') . strtotime('Y-m-d H:i:s') . 'user_id');
    //             $skill               = new SkrillRequest();
    //             $skill->pay_to_email = $this->email;
    //             $skill->return_url   = route(
    //                 'invoice.skrill', [
    //                                     $request->invoice_id,
    //                                     $price,
    //                                     'tansaction_id=' . MD5($request['transaction_id']),
    //                                 ]
    //             );

    //             $skill->cancel_url   = route(
    //                 'invoice.skrill', [
    //                 $request->invoice_id,
    //                 $price,
    //             ]
    //             );

    //             // create object instance of SkrillRequest
    //             $skill->transaction_id  = MD5(MD5($request['transaction_id'])); // generate transaction id
    //             $skill->amount          = $price;
    //             $skill->currency        = Utility::getValByName('site_currency');
    //             $skill->language        = 'EN';
    //             $skill->prepare_only    = '1';
    //             $skill->merchant_fields = 'site_name, customer_email';
    //             $skill->site_name       = $user->name;
    //             $skill->customer_email  = $user->email;

    //             // create object instance of SkrillClient
    //             $client = new SkrillClient($skill);

    //             $sid    = $client->generateSID(); //return SESSION ID

    //             // handle error
    //             $jsonSID = json_decode($sid);
    //             if($jsonSID != null && $jsonSID->code == "BAD_REQUEST")
    //             {
    //                 return redirect()->back()->with('error', $jsonSID->message);
    //             }


    //             // do the payment
    //             $redirectUrl = $client->paymentRedirectUrl($sid); //return redirect url
    //             if($tran_id)
    //             {
    //                 $data = [
    //                     'amount' => $price,
    //                     'trans_id' => MD5($request['transaction_id']),
    //                     'currency' => Utility::getValByName('site_currency'),
    //                 ];
    //                 session()->put('skrill_data', $data);
    //             }
    //             return redirect($redirectUrl);

    //         }
    //         else
    //         {
    //             $res['msg']  = __("Enter valid amount.");
    //             $res['flag'] = 2;

    //             return $res;
    //         }

    //     }
    //     else
    //     {
    //         return redirect()->route('invoice.index')->with('error', __('Invoice is deleted.'));

    //     }


    // }

    // public function getInvoicePaymentStatus(Request $request, $invoice_id, $amount)
    // {
    //     $invoiceID =  \Illuminate\Support\Facades\Crypt::decrypt($invoice_id);
    //     $invoice   = Invoice::find($invoiceID);
    //     if(\Auth::check())
    //     {
    //         $user=\Auth::user();
    //     }
    //     else
    //     {
    //         $user= User::where('id',$invoice->created_by)->first();
    //     }

    //     $payment  = $this->paymentConfig($user);
    //     $orderID  = strtoupper(str_replace('.', '', uniqid('', true)));
    //     $settings = DB::table('settings')->where('created_by', '=', $user->creatorId())->get()->pluck('value', 'name');

    //     $invoiceID =  \Illuminate\Support\Facades\Crypt::decrypt($invoice_id);
    //     $invoice   = Invoice::find($invoiceID);
    //     $result    = array();

    //     if($invoice)
    //     {

    //         // try
    //         // {
    //             if(session()->has('skrill_data'))
    //             {
    //                 $get_data = session()->get('skrill_data');

    //                 $payments = InvoicePayment::create(
    //                     [
    //                         'invoice' => $invoice->id,
    //                         'date' => date('Y-m-d'),
    //                         'amount' => $amount,
    //                         'payment_method' => 1,
    //                         'transaction' => $orderID,
    //                         'payment_type' => __('Skrill'),
    //                         'receipt' => '',
    //                         'notes' => __('Invoice') . ' ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id),
    //                     ]
    //                 );

    //                 $invoice = Invoice::find($invoice->id);

    //                 if($invoice->getDue() <= 0.0)
    //                 {
    //                     Invoice::change_status($invoice->id, 5);
    //                 }
    //                 elseif($invoice->getDue() > 0)
    //                 {
    //                     Invoice::change_status($invoice->id, 4);
    //                 }
    //                 else
    //                 {
    //                     Invoice::change_status($invoice->id, 3);
    //                 }
    //                 if(\Auth::check())
    //                 {
    //                      $user = Auth::user();
    //                 }
    //                 else
    //                 {
    //                    $user=User::where('id',$invoice->created_by)->first();
    //                 }
    //                 $settings  = Utility::settings();
    //                 if(isset($settings['payment_create_notification']) && $settings['payment_create_notification'] ==1){

    //                     $msg = __('New payment of ').$amount.' '.__('created for ').$user->name.__(' by Skrill').'.';
    //                     Utility::send_slack_msg($msg);

    //                 }
    //                 if(isset($settings['telegram_payment_create_notification']) && $settings['telegram_payment_create_notification'] ==1){
    //                         $resp = __('New payment of ').$amount.' '.__('created for ').$user->name.__(' by Skrill').'.';
    //                             Utility::send_telegram_msg($resp);
    //                 }
    //                 $client_namee = Client::where('user_id',$invoice->client)->first();
    //                 if(isset($settings['twilio_invoice_payment_create_notification']) && $settings['twilio_invoice_payment_create_notification'] ==1)
    //                 {
    //                      $message = __('New payment of ').$amount.' '.__('created for ').$user->name.__(' by Skrill').'.';
    //                      Utility::send_twilio_msg($client_namee->mobile,$message);
    //                 }

    //                 return redirect()->back()->with('success', __(' Payment successfully added.'));


    //             }
    //             else
    //             {
    //                 return redirect()->back()->with('error', __('Transaction has been failed! '));
    //             }
    //             // }
    //             // catch(\Exception $e)
    //             // {
    //             //     return redirect()->back()->with('error', __('Invoice not found!'));
    //             // }
    //     }
    // }

}

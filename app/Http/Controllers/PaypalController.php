<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Order;
use App\Models\Plan;
use App\Models\UserCoupon;
use App\Models\User;
use App\Models\Utility;
use App\Models\Client;
use App\Models\transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use PayPal\Api\Amount;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
// use PayPal\Rest\ApiContext;

use Srmklive\PayPal\Services\PayPal as PayPalClient;


class PaypalController extends Controller
{
    private $_api_context;



    public function paymentConfig($user)
    {
        if(\Auth::check())
        {
            $payment_setting = Utility::getAdminPaymentSetting();
        }
        else
        {
            $payment_setting = Utility::getCompanyPaymentSetting();
        }


        if ($payment_setting['paypal_mode'] == 'live') {
            config([
                'paypal.live.client_id' => isset($payment_setting['paypal_client_id']) ? $payment_setting['paypal_client_id'] : '',
                'paypal.live.client_secret' => isset($payment_setting['paypal_secret_key']) ? $payment_setting['paypal_secret_key'] : '',
                'paypal.mode' => isset($payment_setting['paypal_mode']) ? $payment_setting['paypal_mode'] : '',
            ]);
        } else {
            config([
                'paypal.sandbox.client_id' => isset($payment_setting['paypal_client_id']) ? $payment_setting['paypal_client_id'] : '',
                'paypal.sandbox.client_secret' => isset($payment_setting['paypal_secret_key']) ? $payment_setting['paypal_secret_key'] : '',
                'paypal.mode' => isset($payment_setting['paypal_mode']) ? $payment_setting['paypal_mode'] : '',
            ]);
        }

    }
    public function planPayWithPaypal(Request $request)
    {
        $planID = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);

        $plan   = Plan::find($planID);
        // dd($plan);
        $user = Auth::user();
        $this->paymentconfig($user);
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $get_amount = $plan->price;
        // dd($get_amount);
        if($plan){
            try
            {
                $coupon_id = null;
                $price     = $plan->price;
                if(!empty($request->coupon))
                {
                    $coupons = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                    if(!empty($coupons))
                    {
                        $usedCoupun     = $coupons->used_coupon();
                        $discount_value = ($plan->price / 100) * $coupons->discount;
                        $price          = $plan->price - $discount_value;
                        if($coupons->limit == $usedCoupun)
                        {
                            return redirect()->back()->with('error', __('This coupon code has expired.'));
                        }
                        $coupon_id = $coupons->id;
                    }
                    else
                    {
                        return redirect()->back()->with('error', __('This coupon code is invalid or has expired.'));
                    }
                }
                $this->paymentConfig($user);
                $payment_setting = Utility::getAdminPaymentSetting();

                $paypalToken = $provider->getAccessToken();
                $response = $provider->createOrder([
                    "intent" => "CAPTURE",
                    "application_context" => [
                        "return_url" => route('plan.get.payment.status',[$plan->id,$get_amount]),
                        "cancel_url" =>  route('plan.get.payment.status',[$plan->id,$get_amount]),
                    ],
                    "purchase_units" => [
                        0 => [
                            "amount" => [
                                "currency_code" =>  $payment_setting['currency'] ? $payment_setting['currency'] : '',
                                "value" => $get_amount
                            ]
                        ]
                    ]
                ]);
// dd($payment_setting['currency']);
                if (isset($response['id']) && $response['id'] != null) {
                    // redirect to approve href
                    foreach ($response['links'] as $links) {
                        if ($links['rel'] == 'approve') {
                            return redirect()->away($links['href']);
                        }
                    }
                    return redirect()
                        ->route('plan.index')
                        ->with('error', 'Something went wrong.');
                } else {
                    // dd($response);
                    return redirect()
                        ->route('plan.index')
                        ->with('error', $response['message'] ?? 'Something went wrong.');
                }
            }
            catch(\Exception $e)
            {

                return redirect()->route('plan.index')->with('error', __($e->getMessage()));
            }
        }else{
            return redirect()->route('plan.index')->with('error', __('Plan is deleted.'));
        }
    }

    public function planGetPaymentStatus(Request $request, $plan_id, $amount)
    {
        // dd($request->all());
        $user = Auth::user();
        $plan = Plan::find($plan_id);


        if($plan)
        {

            $payment_setting = Utility::getAdminPaymentSetting();

            $this->paymentConfig($user);

            $provider = new PayPalClient;

            $user    = \Auth::user();
            $referral = DB::table('referral_programs')->first();
            $amount =  ($plan->price * $referral->commission) / 100;
            $referral = DB::table('referral_programs')->first();
            $transactions = transactions::where('uid', $user->id)->get();
            $total = count($transactions);

            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            $response = $provider->capturePaymentOrder($request['token']);
            // dd($response);
            $payment_id = Session::get('paypal_payment_id');
            // dd($payment_id);
            $order_id = strtoupper(str_replace('.', '', uniqid('', true)));
            if (isset($response['status']) && $response['status'] == 'COMPLETED')
            {
                // dd($response['status']);
                if($response['status'] == 'COMPLETED'){
                   $statuses = 'success';
                }
                    $order                 = new Order();
                    $order->order_id       = $order_id;
                    $order->name           = $user->name;
                    $order->card_number    = '';
                    $order->card_exp_month = '';
                    $order->card_exp_year  = '';
                    $order->plan_name      = $plan->name;
                    $order->plan_id        = $plan->id;
                    $order->price          = $amount;
                    $order->price_currency = $payment_setting['currency'] ? $payment_setting['currency'] : '';
                    $order->txn_id         = '';
                    $order->payment_type   = 'PAYPAL';
                    $order->payment_status = $statuses;
                    $order->receipt        = '';
                    $order->user_id        = $user->id;
                    $order->save();




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
                    return redirect()->route('plan.index')->with('success', __('Plan activated Successfully.'));
                }
                else
                {
                    return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
                }

                return redirect()
                    ->route('plan.index')
                    ->with('success', 'Transaction complete.');
            } else {
                return redirect()
                    ->route('plan.index')
                    ->with('error', $response['message'] ?? 'Something went wrong.');
            }
        }
        else
        {
            return redirect()->route('plan.index')->with('error', __('Plan is deleted.'));
        }
    }

    public function clientPayWithPaypal(Request $request, $invoice_id)
    {
        $user = Auth::user();
        $this->paymentConfig($user);
        $invoice                 = Invoice::find($invoice_id);
        $this->invoiceData       = $invoice;
        $settings                = DB::table('settings')->where('created_by', '=', $invoice->created_by)->get()->pluck('value', 'name');
        $get_amount = $request->amount;
        $request->validate(['amount' => 'required|numeric|min:0']);

        $payment_setting = Utility::getCompanyPaymentSettingWithOutAuth($invoice->created_by);
        config(
            [
                'paypal.sandbox.client_id' => isset($payment_setting['paypal_client_id']) ? $payment_setting['paypal_client_id'] : '',
                'paypal.sandbox.client_secret' => isset($payment_setting['paypal_secret_key']) ? $payment_setting['paypal_secret_key'] : '',
                'paypal.mode' => isset($payment_setting['paypal_mode']) ? $payment_setting['paypal_mode'] : '',
            ]
        );
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));

//        $invoice = Invoice::find($invoice_id);

        if($invoice)
        {
            if($get_amount > $invoice->getDue())
            {
                return redirect()->back()->with('error', __('Invalid amount.'));
            }
            else
            {


                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                $name = Utility::invoiceNumberFormat($settings, $invoice->invoice_id);


                $paypalToken = $provider->getAccessToken();
                $response = $provider->createOrder([
                    "intent" => "CAPTURE",
                    "application_context" => [
                        "return_url" => route('client.get.payment.status',[$invoice->id,$get_amount]),
                        "cancel_url" =>  route('client.get.payment.status',[$invoice->id,$get_amount]),
                    ],
                    "purchase_units" => [
                        0 => [
                            "amount" => [
                                "currency_code" => !empty(env('CURRENCY')) ? env('CURRENCY') : 'USD',
                                "value" => $get_amount
                            ]
                        ]
                    ]
                ]);
              //  dd($response);
                if (isset($response['id']) && $response['id'] != null) {
                    // redirect to approve href
                    foreach ($response['links'] as $links) {
                        if ($links['rel'] == 'approve') {
                            return redirect()->away($links['href']);
                        }
                    }
                    return redirect()
                        ->route('invoice.show', \Crypt::encrypt($invoice->id))
                        ->with('error', 'Something went wrong.');
                } else {
                    return redirect()
                        ->route('invoice.show', \Crypt::encrypt($invoice->id))
                        ->with('error', $response['message'] ?? 'Something went wrong.');
                }



//
                return redirect()->back()->with('error', __('Unknown error occurred'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


     public function clientGetPaymentStatus(Request $request, $invoice_id,$amount)
    {
        $invoice = Invoice::find($invoice_id);
        if(\Auth::check())
        {
            $user=\Auth::user();
        }
        else
        {
            $user= User::where('id',$invoice->created_by)->first();
        }

        $settings = DB::table('settings')->where('created_by', '=', $user->creatorId())->get()->pluck('value', 'name');
        $this->paymentConfig($user);
        //$user     = Auth::user();



        // $this->setApiContext($user);

        $payment_id = Session::get('paypal_payment_id');

        Session::forget('paypal_payment_id');

        if(empty($request->PayerID || empty($request->token)))
        {
            return redirect()->route(
                'invoice.show', $invoice_id
            )->with('error', __('Payment failed'));
        }


            $order_id = strtoupper(str_replace('.', '', uniqid('', true)));


                    $payments = New InvoicePayment;
                    $payments->transaction = $order_id;
                    $payments->invoice = $invoice->id;
                    $payments->amount = $amount;
                    $payments->date = date('Y-m-d');
                    $payments->payment_method =__('Paypal');
                    $payments->payment_type = __('Paypal');
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
                if (isset($settings['payment_create_notification']) && $settings['payment_create_notification'] == 1) {
                    $uArr = [
                        'user_name' => $user->name,
                        'amount'=> $amount,
                        'created_by'=> 'by Paypal',
                    ];
                    Utility::send_slack_msg('new_payment', $uArr);
                    }
                    if (isset($settings['telegram_payment_create_notification']) && $settings['telegram_payment_create_notification'] == 1) {
                        $uArr = [
                            'user_name' => $user->name,
                            'amount'=> $amount,
                            'created_by'=> 'by Paypal',
                        ];
                        Utility::send_telegram_msg('new_payment', $uArr);
                        }
                    if (isset($settings['twilio_invoice_payment_create_notification']) && $settings['twilio_invoice_payment_create_notification'] == 1) {
                        $uArr = [
                            'user_name' => $user->name,
                            'amount'=> $amount,
                            'created_by'=> 'by Paypal',
                        ];
                        Utility::send_twilio_msg('new_payment', $uArr);
                        }
                        //webhook
            $module = "New payment";
            $webhook = Utility::webhookSetting($module,$invoice->created_by);
            if($webhook)
            {
                $parameter = json_encode($invoice);

                // 1 parameter is URL , 2  (payment Data) parameter is data , 3 parameter is method
                $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                if($status == true)
                {
                    return redirect()->route('invoice.show', \Crypt::encrypt($invoice_id))->with('success', __('Payment successfully added'));
                }
                else
                {
                    return redirect()->back()->with('error', __('payment Call Failed.'));
                }
            }

                if(\Auth::check())
                {
                    return redirect()->route('invoice.show', \Crypt::encrypt($invoice_id))->with('success', __('Payment successfully added'));
                }
                else
                {

                    return redirect()->route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('success', __('Payment successfully added'));
                }


    }


}

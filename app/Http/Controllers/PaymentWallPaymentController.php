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


class PaymentWallPaymentController extends Controller
{
    public $secret_key;
    public $public_key;
    public $is_enabled;

    public function paymentwall(Request $request){
        $data = $request->all();
        $admin_payment_setting = Utility::getAdminPaymentSetting();
        $settings = Utility::settings();
        return view('plan.paymentwall',compact('data','admin_payment_setting','settings'));
    }
    public function paymentConfig($user)
    {
        if(Auth::check()){
            $user = Auth::user();
        }
        if($user->type == 'company')
        {
            $payment_setting = Utility::getAdminPaymentSetting();
        }
        else
        {
            $payment_setting = Utility::getCompanyPaymentSetting();
        }

        $this->secret_key = isset($payment_setting['paymentwall_private_key ']) ? $payment_setting['paymentwall_private_key  '] : '';
        $this->public_key = isset($payment_setting['paymentwall_public_key']) ? $payment_setting['paymentwall_public_key'] : '';
        $this->is_enabled = isset($payment_setting['is_paymentwall_enabled']) ? $payment_setting['is_paymentwall_enabled'] : 'off';

        return $this;
    }

    public function planPayWithPaymentWall(Request $request,$plan_id)
    {

        $planID    = \Illuminate\Support\Facades\Crypt::decrypt($plan_id);

        // $res['msg'] = __("error");
        // $res['plan']=$planID;
        // return $res;

        $plan      = Plan::find($planID);
        $authuser  = Auth::user();
        $coupon_id = '';

        if($plan)
        {
            $price = $plan->price;
            if($price <= 0)
            {
                $authuser->plan = $plan->id;
                $authuser->save();

                $assignPlan = $authuser->assignPlan($plan->id);

                if($assignPlan['is_success'] == true && !empty($plan))
                {
                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                    // dd($orderID);
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
            }
                else {

                    $orderID = time();
                    \Paymentwall_Config::getInstance()->set(array(
                        'private_key' => 'sdrsefrszdef'
                    ));
                    $parameters = $request->all();
                    $chargeInfo = array(
                        'email' => $parameters['email'],
                        'history[registration_date]' => '1489655092',
                        'amount' => $price,
                        'currency' => !empty($this->currancy) ? $this->currancy : 'USD',
                        'token' => $parameters['brick_token'],
                        'fingerprint' => $parameters['brick_fingerprint'],
                        'description' => 'Order #123'
                    );
                    $charge = new \Paymentwall_Charge();
                    $charge->create($chargeInfo);
                    $responseData = json_decode($charge->getRawResponseData(),true);
                    $response = $charge->getPublicData();

                    if ($charge->isSuccessful() AND empty($responseData['secure'])) {
                        if ($charge->isCaptured()) {
                           if($request->has('coupon') && $request->coupon != '')
                            {
                                $coupons = Coupon::find($request->coupon);
                                if(!empty($coupons))
                                {
                                    $userCoupon            = new UserCoupon();
                                    $userCoupon->user   = $authuser->id;
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
                            $orderID=time();
                            $order                 = new Order();
                            $order->order_id       = $orderID;
                            $order->name           = $authuser->name;
                            $order->card_number    = '';
                            $order->card_exp_month = '';
                            $order->card_exp_year  = '';
                            $order->plan_name      = $plan->name;
                            $order->plan_id        = $plan->id;
                            $order->price          = isset($paydata['amount']) ? $paydata['amount'] : $price;
                            $order->price_currency = $this->currancy;
                            $order->txn_id         = isset($paydata['txid']) ? $paydata['txid'] : 0;
                            $order->payment_type   = __('PaymentWall');
                            $order->payment_status = 'success';
                            $order->receipt        = '';
                            $order->user_id        = $authuser->id;
                            $order->save();


                            $plan    = Plan::find($planID);
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


                            $assignPlan = $authuser->assignPlan($plan->id);
                            if($assignPlan['is_success'])
                            {
                                $res['msg'] = __("Plan successfully upgraded.");
                                 $res['flag'] = 1;
                                 return $res;
                            }
                        } elseif ($charge->isUnderReview()) {
                            // decide on risk charge
                        }
                    } elseif (!empty($responseData['secure'])) {
                        $response = json_encode(array('secure' => $responseData['secure']));
                    } else {
                        $errors = json_decode($response, true);
                                 $res['flag'] = 2;
                                 return $res;
                    }
                    echo $response;

                }

        }

    }
    public function planeerror(Request $request,$flag)
    {
        if($flag == 1){
            return redirect()->route("plan.index")->with('error', __('Transaction has been Successfull! '));
        }else{

                return redirect()->route("plan.index")->with('error', __('Transaction has been failed! '));
        }

    }


    public function invoicepaymentwall(Request $request){
        $data = $request->all();
        $company_payment_setting = Utility::getCompanyPaymentSetting();
        $settings = Utility::settings();
        return view('invoice.paymentwall',compact('data','company_payment_setting','settings'));
    }



    public function invoiceerror(Request $request,$flag,$invoice_id)
    {
        if($flag == 1)
        {
            return redirect()->route('invoice.show',\Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('error', __('Payment successfully added. '));
        }
        else
        {
            return redirect()->route("invoice.show",\Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('error', __('Transaction has been failed! '));
        }
    }


    public function invoicePayWithPaymentwall(Request $request,$invoiceID)
    {
        $invoiceID = \Crypt::decrypt($invoiceID);

        // $res['msg'] = __("error");
        // $res['invoice']=$invoiceID;
        // return $res;

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
            // dd($price);
            if($price < 0)
            {
                $res_data['email']       = $user->email;
                $res_data['total_price'] = $price;
                $res_data['currency']    = Utility::getValByName('site_currency');
                $res_data['flag']        = 1;
                // return $res_data;
            }
            else
            {
                $authuser = Auth::user();
                \Paymentwall_Config::getInstance()->set(array(
                    'private_key' => 'sdrsefrszdef'
                ));
                $parameters = $request->all();
                $chargeInfo = array(
                    'email' => $parameters['email'],
                    'history[registration_date]' => '1489655092',
                    'amount' => $price,
                    'currency' => !empty($this->currancy) ? $this->currancy : 'USD',
                    'token' => $parameters['brick_token'],
                    'fingerprint' => $parameters['brick_fingerprint'],
                    'description' => 'Order #123'
                );
                $charge = new \Paymentwall_Charge();
                $charge->create($chargeInfo);
                $responseData = json_decode($charge->getRawResponseData(),true);
                $response = $charge->getPublicData();
                // dd($response);
                if ($charge->isSuccessful() AND empty($responseData['secure'])) {
                    if ($charge->isCaptured()) {

                        $settings = DB::table('settings')->where('created_by', '=', $user->creatorId())->get()->pluck('value', 'name');
                        $orderID = time();
                        $payments = New InvoicePayment;
                        $payments->transaction = $orderID;
                        $payments->invoice = $invoice->id;
                        $payments->amount = isset($request['amount'])?$request['amount']:0;
                        $payments->date = date('Y-m-d');
                        $payments->payment_method = 1;
                        $payments->payment_type = __('PaymentWall');
                        $payments->notes = __('Invoice') . ' ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id);
                        $payments->receipt = '';
                        $payments->created_by = \Auth::user()->creatorId();
                        $payments->save();
                        // dd($payments);
                        $invoice = Invoice::find($invoice->id);

                        $invoice_getdue = number_format((float)$invoice->getDue(), 2, '.', '');
                        if($invoice_getdue <= 0.0)
                        {
                            Invoice::change_status($invoice->id, 3);
                        }
                        else{
                            Invoice::change_status($invoice->id, 2);
                        }

                        $assignPlan = $authuser->assignPlan($invoice->id);
                        if($assignPlan['is_success'])
                        {
                            $res['msg'] = __("Invoice successfully .");
                            $res['flag'] = 1;
                            return $res;
                        }
                    } elseif ($charge->isUnderReview()) {
                        // decide on risk charge
                    }
                } elseif (!empty($responseData['secure'])) {
                    $response = json_encode(array('secure' => $responseData['secure']));
                } else {
                    $errors = json_decode($response, true);
                            $res['invoice']=$invoiceID;
                            $res['flag'] = 2;
                            return $res;
                }
                echo $response;

            }
        }

    }
}



<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Invoice;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Setting;
use App\Models\User;
use App\Models\Plan;
use App\Models\Order;
use App\Models\transactions;
use App\Models\UserCoupon;
use App\Models\Utility;
use Composer\Semver\Interval;
use Exception;
use Illuminate\Support\Facades\DB;

class FedapayController extends Controller
{
    public $stripe_key;
    public $stripe_secret;
    public $is_stripe_enabled;
    public $currancy;


    public function planPaywithfedapay(Request $request)
    {
        $payment_setting = Utility::getAdminPaymentSetting();
        $currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'XOF';
        $planID = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);

        $plan = Plan::find($planID);
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        $authuser = Auth::user();

        if ($plan) {
            $net = $plan->price;
            $get_amount = intval($net);


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


            if (!empty($request->coupon)) {
                $response = ['get_amount' => $get_amount, 'plan' => $plan, 'coupon_id' => $coupons->id];
            } else {
                $response = ['get_amount' => $get_amount, 'plan' => $plan];
            }
            try {
                $fedapay = isset($payment_setting['Fedapay_secret_key']) ? $payment_setting['Fedapay_secret_key'] : '';
                $fedapay_mode = !empty($payment_setting['Fedapay_mode']) ? $payment_setting['Fedapay_mode'] : 'sandbox';
                // dd($fedapay,$fedapay_mode);
                \FedaPay\FedaPay::setApiKey($fedapay);
                \FedaPay\FedaPay::setEnvironment($fedapay_mode);

                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                // dd($orderID,$planID);

                $transaction = \FedaPay\Transaction::create([
                    "description" => "Fedapay Payment",
                    "amount" => $get_amount,
                    "currency" => ["iso" => $currency],

                    "callback_url" => route('plan.get.fedapay.status', [
                        'order_id' => $orderID,
                        'plan_id' => $planID,
                        'coupon_code' => !empty($request->coupon) ? $request->coupon : '',
                        'net_price' => $get_amount,
                    ]),
                    "cancel_url" => route('plan.get.fedapay.status', [
                        'order_id' => $orderID,
                        'plan_id' => $planID,
                        'coupon_code' => !empty($request->coupon) ? $request->coupon : '',
                    ]),

                ]);
                // dd($transaction);
                $token = $transaction->generateToken();
                // dd($token);

                return redirect($token->url);
            } catch (\Exception $e) {

                return redirect()->route('plan.index')->with('error', $e->getMessage());
            }
        } else {
            return redirect()->route('plan.index')->with('error', __('Plan is deleted.'));
        }
    }

    public function planGetFedapayStatus(Request $request, $plan_id)
    {
        try {
            if ($request->status == "approved") {
                $data = request()->all();
                $getAmount = $request->net_price;
                $authuser = Auth::user();
                // dd($authuser->id);
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
                        'price' => $getAmount,
                        'price_currency' => isset($admin_settings['defult_currancy']) ? $admin_settings['defult_currancy'] : '',
                        'txn_id' => '',
                        'payment_type' => __('Fedapay'),
                        'payment_status' => 'succeeded',
                        'receipt' => null,
                        'user_id' => $authuser->id,
                    ]
                );


                $assignPlan = $authuser->assignPlan($plan->id);

                $coupons = Coupon::where('code', $request->coupon_code)->first();

                if (!empty($request->coupon_code)) {
                    if (!empty($coupons)) {
                        $userCoupon = new UserCoupon();
                        $userCoupon->user= $authuser->id;
                        $userCoupon->coupon= $coupons->id;
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
                    $user    = \Auth::user();
                    $referral = DB::table('referral_programs')->first();
                    if($referral != null && isset($referral->commission)){
                   $amount=  ($getAmount * $referral->commission) /100;
                   $referral = DB::table('referral_programs')->first();
                   $transactions = transactions::where('uid', $user->id)->get();
                   $total=count($transactions);
                   if (isset($referral) && $referral->Reffral_enabled == "on") {

                  if($user->used_referral_code !== null && $total == 0)
                   {
                    // dd($data);


                       transactions::create(
                           [
                               'referral_code' => $user->referral_code,
                               'used_referral_code' => $user->used_referral_code,
                               'company_name' => $user->name,
                               'plane_name' => $plan->name,
                               'plan_price'=> $getAmount,
                               'commission'=>$referral->commission,
                               'commission_amount'=>$amount,
                               'uid' => $user->id,
                           ]
                       );
                   }

                    return redirect()->route('plan.index')->with('success', __('Plan activated Successfully!'));
                } else {
                    // dd($assignPlan);
                    return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
                }
            }
            } else {

                return redirect()->route('plan.index')->with('error', __('Payment failed'));
            }
        }
        return redirect()->route('plan.index')->with('error', __('Payment failed'));
        } catch (\Exception $e) {
            // dd($e);
            return redirect()->route('plan.index')->with('error', $e->getMessage());
        }

}

    public function invoicePayWithFedapay(Request $request)
    {
        // dd($request);
        $validator = Validator::make(
            $request->all(),
            ['amount' => 'required|numeric', 'invoice_id' => 'required']
        );
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }
        $invoice_id = $request->input('invoice_id');
        $type = "invoice";
        // dd($invoice_id,$type);

        if ($type == 'invoice') {

            $invoiceID = $request->invoice_id;
            $invoiceID = \Crypt::decrypt($invoiceID);
            $invoice   = Invoice::find($invoiceID);

            if (\Auth::check()) {
                $user = \Auth::user();
            } else {
                $user = User::where('id', $invoice->created_by)->first();
            }

            $user_id = $invoice->created_by;

            $payment_id = $invoice->id;





            $company_settings = Utility::payment_settings($user_id);
            $company_currancy = !empty($company_settings['defult_currancy']) ? $company_settings['defult_currancy'] : 'XOF';
            $fedapay = !empty($company_settings['Fedapay_secret_key']) ? $company_settings['Fedapay_secret_key'] : '';
            $fedapay_mode = !empty($company_settings['company_fedapay_mode']) ? $company_settings['company_fedapay_mode'] : 'sandbox';
            $user = User::find($user_id);
            $get_amount = $request->amount;


            if ($invoice) {
                if ($get_amount > $invoice->getDue()) {
                    return redirect()->back()->with('error', __('Invalid amount.'));
                } else {
                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                    try {

                        \FedaPay\FedaPay::setApiKey($fedapay);
                        \FedaPay\FedaPay::setEnvironment($fedapay_mode);

                        $transaction = \FedaPay\Transaction::create([
                            "description" => "Fedapay Payment",
                            "amount" => $get_amount,
                            "currency" => ["iso" => "XOF"],
                            "callback_url" => route(
                                'invoice.fedapay',
                                [
                                    'invoice_id' => $invoice_id,
                                    'amount' => $get_amount,
                                    'type' => $type
                                ]
                            ),
                            "cancel_url" =>  route(
                                'invoice.fedapay',
                                [
                                    'invoice_id' => $invoice_id,
                                    'amount' => $get_amount,
                                    'type' => $type
                                ]
                            )
                        ]);

                        $token = $transaction->generateToken();

                        return redirect($token->url);
                    } catch (Exception $e) {
                        if ($request->type == 'invoice') {
                            return redirect()->route('invoice.show', $invoice_id)->with('error', $e->getMessage() ?? 'Something went wrong.');
                        } elseif ($request->type == 'salesinvoice') {
                            return redirect()->route('salesinvoice.show', $invoice_id)->with('error', $e->getMessage() ?? 'Something went wrong.');
                        } elseif ($request->type == 'retainer') {
                            return redirect()->route('retainer.show', $invoice_id)->with('error', $e->getMessage() ?? 'Something went wrong.');
                        } elseif ($type == 'feesreceipt') {
                            return redirect()->route('pay.fees', Crypt::encrypt($invoice_id))->with('error', $e->getMessage() ?? 'Something went wrong.');
                        }
                    }
                    return redirect()->back()->with('error', __('Unknown error occurred'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
    }

    public function getInvoicePaymentStatus(Request $request, $invoice_id, $amount, $type)
    {
        try {
            if ($request->status == "approved") {
                if ($type == 'invoice') {
                    $invoice_id = $request->invoice_id;
                    $invoice_id = \Crypt::decrypt($invoice_id);
                    $invoice   = Invoice::find($invoice_id);
                    if (\Auth::check()) {
                        $user = \Auth::user();
                    } else {
                        $user = User::where('id', $invoice->created_by)->first();
                    }

                    $company_settings = Utility::payment_settings();


                    $this->currancy = isset($company_settings['defult_currancy']) ? $company_settings['defult_currancy'] : '$';

                    $settings  = DB::table('settings')->where('created_by', '=', $user->creatorId())->get()->pluck('value', 'name');

                    if ($invoice) {
                        if (empty($request->PayerID || empty($request->token))) {
                            return redirect()->route('invoice.show', $invoice_id)->with('error', __('Payment failed'));
                        }
                        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                        try {
                            $invoice_payment = new \App\Models\InvoicePayment();
                            $invoice_payment->invoice = $invoice_id;
                            $invoice_payment->date = Date('Y-m-d');
                            $invoice_payment->notes = __('Invoice') . ' ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id);
                            $invoice_payment->payment_method =  __('Fedapay');
                            $invoice_payment->amount = $amount;
                            $invoice_payment->transaction = $orderID;
                            $invoice_payment->payment_type = 'Fedapay';
                            $invoice_payment->created_by = $user->creatorId();
                            $invoice_payment->save();

                            $invoice = Invoice::find($invoice->id);


                            $due = $invoice->getDue();
                            if ($due <= 0) {
                                $invoice->status = 4;
                                $invoice->save();
                            } else {
                                $invoice->status = 3;
                                $invoice->save();
                            }


                            return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Invoice paid Successfully!'));
                        } catch (\Exception $e) {
                            dd($e);
                            return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', $e->getMessage());
                        }
                    } else {
                        return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Invoice not found.'));
                    }
                }
            } else {
                return redirect()->back()->with('error', __('Transaction has been failed.'));
            }
        } catch (\Exception $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }
    }
}

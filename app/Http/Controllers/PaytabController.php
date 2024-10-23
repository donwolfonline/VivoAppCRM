<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utility;
use Paytabscom\Laravel_paytabs\Facades\paypage;
use App\Models\Plan;
use App\Models\PlanOrder;
use App\Models\UserCoupon;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariantOption;
use App\Models\PurchasedProducts;
use App\Models\ProductCoupon;
use App\Models\Store;
use App\Models\Shipping;
use App\Models\transactions;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Exception;
use Illuminate\Support\Facades\DB;

class PaytabController extends Controller
{
    public $paytab_profile_id, $paytab_server_key, $paytab_region, $is_enabled;

    public function paymentConfig()
    {
        if (\Auth::check()) {
            $payment_setting = Utility::getAdminPaymentSetting();
            config([
                'paytabs.profile_id' => isset($payment_setting['paytab_profile_id']) ? $payment_setting['paytab_profile_id'] : '',
                'paytabs.server_key' => isset($payment_setting['paytab_server_key']) ? $payment_setting['paytab_server_key'] : '',
                'paytabs.region' => isset($payment_setting['paytab_region']) ? $payment_setting['paytab_region'] : '',
                'paytabs.currency' => Utility::getAdminCurrency()
            ]);
        }
    }
    public function paymentSetting($id)
    {
        $payment_setting = Utility::getCompanyPaymentSetting($id);


        config([
            'paytabs.profile_id' => isset($payment_setting['paytab_profile_id']) ? $payment_setting['paytab_profile_id'] : '',
            'paytabs.server_key' => isset($payment_setting['paytab_server_key']) ? $payment_setting['paytab_server_key'] : '',
            'paytabs.region' => isset($payment_setting['paytab_region']) ? $payment_setting['paytab_region'] : '',
            'paytabs.currency' =>  Utility::getValByName('site_currency')

        ]);
    }
    public function planPayWithpaytab(Request $request)
    {

        try {
            $planID = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
            $plan = Plan::find($planID);
            $this->paymentconfig();
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
                                if (!empty($authuser->payment_subscription_id) && $authuser->payment_subscription_id != '') {
                                    try {
                                        $authuser->cancel_subscription($authuser->id);
                                    } catch (\Exception $exception) {
                                        \Log::debug($exception->getMessage());
                                    }
                                }
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
                                        'price_currency' => Utility::getAdminCurrency(),
                                        'txn_id' => '',
                                        'payment_type' => 'Paytab',
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
                $coupon = (empty($request->coupon)) ? "0" : $request->coupon;
                    $pay = paypage::sendPaymentCode('all')
                        ->sendTransaction('sale')
                        ->sendCart(1, $get_amount, 'plan payment')
                        ->sendCustomerDetails(isset($user->name) ? $user->name : "", isset($user->email) ? $user->email : '', '', '', '', '', '', '', '')
                        ->sendURLs(
                            route('plan.paytab.success', ['success' => 1, 'data' => $request->all(), 'plan_id'=>$plan->id, 'amount'=> $get_amount, 'coupon'=> $coupon]),
                            route('plan.paytab.success', ['success' => 0, 'data' => $request->all(), 'plan_id'=>$plan->id, 'amount'=> $get_amount, 'coupon'=> $coupon])
                        )
                        ->sendLanguage('en')
                        ->sendFramed($on = false)
                        ->create_pay_page();
                    return $pay;
            } else {
                return redirect()->route('plan.index')->with('error', __('Plan is deleted.'));
            }
        } catch (Exception $e) {

            return redirect()->route('plan.index')->with('error', __($e->getMessage()));
        }

    }
    public function PaytabGetPayment(Request $request)
    {
        $planId=$request->plan_id;
		$couponCode=$request->coupon;
		$getAmount=$request->amount;


        if ($couponCode != 0) {
            $coupons = Coupon::where('code', strtoupper($couponCode))->where('is_active', '1')->first();
            $request['coupon_id'] = $coupons->id;
        } else {
            $coupons = null;
        }

        $plan = Plan::find($planId);
        $user = auth()->user();
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        try {
            // dd($request->all());
            // if ($request->success == 1) {
            if ($request->respMessage == "Authorised") {
                $order = new Order();
                $order->order_id = $orderID;
                $order->name = $user->name;
                $order->card_number = '';
                $order->card_exp_month = '';
                $order->card_exp_year = '';
                $order->plan_name = $plan->name;
                $order->plan_id = $plan->id;
                $order->price = $getAmount;
                $order->price_currency = !empty($admin_payment_setting['currency']) ? $admin_payment_setting['currency'] : 'INR';
                $order->payment_type = __('Paytab');
                $order->payment_status = 'success';
                $order->txn_id = '';
                $order->receipt = '';
                $order->user_id = $user->id;
                $order->save();


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
                $coupons = Coupon::find($request->coupon_id);
                if (!empty($request->coupon_id)) {
                    if (!empty($coupons)) {
                        $userCoupon = new UserCoupon();
                        $userCoupon->user = $user->id;
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

                if ($assignPlan['is_success']) {
                    return redirect()->route('plan.index')->with('success', __('Plan activated Successfully.'));
                } else {
                    return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
                }

            } else {
                return redirect()->route('plan.index')->with('error', __('Your Transaction is fail please try again'));
            }
        } catch (Exception $e) {
            return redirect()->route('plan.index')->with('error', __($e->getMessage()));
        }
    }
    public function PayWithpaytab(Request $request){
        try {
            $invoice_id = $request->invoice_id;
            $invoice = Invoice::find($invoice_id);
            $this->paymentSetting($invoice->created_by);

            if (\Auth::check()) {
                $user = Auth::user();
            } else {
                $user = User::where('id', $invoice->created_by)->first();
            }
            if ($user->type != 'company') {
                $user = User::where('id', $user->created_by)->first();
            }

            $get_amount = $request->amount;

            if ($invoice && $get_amount != 0) {
                if ($get_amount > $invoice->getDue()) {
                    return redirect()->back()->with('error', __('Invalid amount.'));
                } else {
                    $pay = paypage::sendPaymentCode('all')
                        ->sendTransaction('sale')
                        ->sendCart(1, $get_amount, 'invoice payment')
                        ->sendCustomerDetails(isset($user->name) ? $user->name : "", isset($user->email) ? $user->email : '', '', '', '', '', '', '', '')
                        ->sendURLs(
                            route('invoice.paytab.status', ['success' => 1, 'data' => $request->all(), $invoice->id, 'amount' => $get_amount]),
                            route('invoice.paytab.status', ['success' => 0, 'data' => $request->all(), $invoice->id, 'amount' => $get_amount])
                        )
                        ->sendLanguage('en')
                        ->sendFramed($on = false)
                        ->create_pay_page();
                        return $pay;

                    }
                }
            } catch (Exception $e) {
            return redirect()->back()->with('error', __($e));
        }
    }

    public function PaytabGetPaymentCallback(Request $request, $invoice_id, $amount){

        $invoice = Invoice::find($invoice_id);

        if(\Auth::check())
        {
             $user=\Auth::user();
        }
        else
        {
            $user= User::where('id',$invoice->created_by)->first();
        }

        $this->invoiceData = $invoice;
        //$settings  = DB::table('settings')->where('created_by', '=', $invoice->created_by)->get()->pluck('value', 'name');
        $settings = DB::table('settings')->where('created_by', '=', $user->creatorId())->get()->pluck('value', 'name');

        // dd($settings);
        $order_id  = strtoupper(str_replace('.', '', uniqid('', true)));

                $payments = New InvoicePayment;
                $payments->transaction = $order_id;
                $payments->invoice = $invoice->id;
                $payments->amount = $amount;
                $payments->date = date('Y-m-d');
                $payments->payment_method = 1;
                $payments->payment_type = __('paytab');
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

                //For Notification
                $customer = User::find($invoice->customer_id);
                $setting  = Utility::settingsById($invoice->created_by);
                $notificationArr = [
                    'payment_price' => $amount,
                    'customer_name' => $user->name,
                ];
                //Slack Notification
                if(isset($setting['payment_notification']) && $setting['payment_notification'] ==1)
                {
                    Utility::send_slack_msg('new_invoice_payment', $notificationArr,$invoice->created_by);
                }
                //Telegram Notification
                if(isset($setting['telegram_payment_notification']) && $setting['telegram_payment_notification'] == 1)
                {
                    Utility::send_telegram_msg('new_invoice_payment', $notificationArr,$invoice->created_by);
                }

                //Twilio Notification
                if(isset($setting['twilio_payment_notification']) && $setting['twilio_payment_notification'] ==1)
                {
                    Utility::send_twilio_msg($customer->contact,'new_invoice_payment', $notificationArr,$invoice->created_by);
                }

                //webhook
                $module ='New Invoice Payment';
                $webhook=  Utility::webhookSetting($module,$invoice->created_by);
                if($webhook)
                {
                    $parameter = json_encode($invoice);
                    $status = Utility::WebhookCall($webhook['url'],$parameter,$webhook['method']);
                    if($status == true)
                    {
                        return redirect()->back()->with('success', __('Payment successfully added!'));
                    }
                    else
                    {
                        return redirect()->back()->with('error', __('Webhook call failed.'));
                    }
                }

                if (\Auth::check()) {
                     return redirect()->route('invoice.show', \Crypt::encrypt($invoice_id))->with('success', __('Payment successfully added'));
                }
                else
                {
                    return redirect()->route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('success', __('Payment successfully added'));

                }

    }
}

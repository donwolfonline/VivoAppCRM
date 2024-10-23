<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\UserCoupon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\Utility;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\InvoicePayment;
use App\Models\transactions;
use Illuminate\Support\Facades\DB;

class SspayController extends Controller
{
    public $secretKey, $callBackUrl, $returnUrl, $categoryCode, $is_enabled, $invoiceData,$user;

    // public function setPaymentDetail()
    // {

    //     $payment_setting = Utility::getAdminPaymentSetting();

    //     $this->secretKey = isset($payment_setting['sspay_secret_key']) ? $payment_setting['sspay_secret_key'] : '';
    //     $this->categoryCode                = isset($payment_setting['sspay_category_code']) ? $payment_setting['sspay_category_code'] : '';
    //     $this->is_enabled          = isset($payment_setting['is_sspay_enabled']) ? $payment_setting['is_sspay_enabled'] : 'off';
    //     return $this;

    // }

    // public function setPaymentDetail_client($invoice_id){

    //     $invoice = Invoice::find($invoice_id);

    //     if(Auth::user() != null){
    //         $this->user         = Auth::user();
    //     }else{
    //         $this->user         = Client::where('id',$invoice->client_id)->first();
    //     }

    //     $payment_setting = Utility::getPaymentSetting($this->user->currentWorkspace->id);

    //     $this->secretKey = isset($payment_setting['sspay_secret_key']) ? $payment_setting['sspay_secret_key'] : '';
    //     $this->categoryCode = isset($payment_setting['sspay_category_code']) ? $payment_setting['sspay_category_code'] : '';
    //     $this->is_enabled = isset($payment_setting['is_sspay_enabled']) ? $payment_setting['is_sspay_enabled'] : 'off';

    // }

    public function __construct()
    {
        if (\Auth::check()) {
            $payment_setting = Utility::getAdminPaymentSetting();
        } else {
            $payment_setting = Utility::getCompanyPaymentSetting();
        }

        // $payment_setting = Utility::getCompanyPaymentSetting();
        $this->secretKey = isset($payment_setting['sspay_secret_key']) ? $payment_setting['sspay_secret_key'] : '';

        $this->categoryCode = isset($payment_setting['sspay_category_code']) ? $payment_setting['sspay_category_code'] : '';
        $this->is_enabled = isset($payment_setting['is_sspay_enabled']) ? $payment_setting['is_sspay_enabled'] : 'off';
    }

    public function SspayPaymentPrepare(Request $request)
    {

            $planID = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
            $plan   = Plan::find($planID);

            if ($plan) {

                $get_amount = $plan->price;
                $user = \Auth::user();


                if (!empty($request->coupon)) {
                    $coupons = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                    if (!empty($coupons)) {
                        $usedCoupun     = $coupons->used_coupon();
                        $discount_value = ($plan->price / 100) * $coupons->discount;
                        $get_amount          = $plan->price - $discount_value;

                        if ($coupons->limit == $usedCoupun) {
                            return redirect()->back()->with('error', __('This coupon code has expired.'));
                        }
                    } else {
                        return redirect()->back()->with('error', __('This coupon code is invalid or has expired.'));
                    }
                }
                if($get_amount <= 0)
                {
                    $user->plan = $plan->id;
                    $user->save();

                    $assignPlan = $user->assignPlan($plan->id);

                    if($assignPlan['is_success'] == true && !empty($plan))
                    {
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
                                'price' => $get_amount == null ? 0 : $get_amount,
                                'price_currency' => Utility::getAdminCurrency(),
                                'txn_id' => '',
                                'payment_type' => __('Flutterwave'),
                                'payment_status' => 'succeeded',
                                'receipt' => null,
                                'user_id' => $user->id,
                            ]
                        );
                        $res['msg']  = __("Plan successfully upgraded.");
                        $res['flag'] = 2;

                        return $res;
                    }
                    else
                    {

                        return Utility::error_res(__('Plan fail to upgrade.'));
                    }
                }


                try {


                    $coupon = (empty($request->coupon)) ? "0" : $request->coupon;
                    $this->callBackUrl = route('plan.sspay', [$plan->id, $get_amount, $coupon]);
                    $this->returnUrl = route('plan.sspay', [$plan->id, $get_amount, $coupon]);

                    $Date = date('d-m-Y');
                    $ammount = $get_amount;
                    $billName = $plan->name;
                    $description = $plan->name;
                    $billExpiryDays = 3;
                    $billExpiryDate = date('d-m-Y', strtotime($Date . ' + 3 days'));
                    $billContentEmail = "Thank you for purchasing our product!";

                    $some_data = array(
                        'userSecretKey' => $this->secretKey,
                        'categoryCode' => $this->categoryCode,
                        'billName' => $billName,
                        'billDescription' => $description,
                        'billPriceSetting' => 1,
                        'billPayorInfo' => 1,
                        'billAmount' => 100 * $ammount,
                        'billReturnUrl' => $this->returnUrl,
                        'billCallbackUrl' => $this->callBackUrl,
                        'billExternalReferenceNo' => 'AFR341DFI',
                        'billTo' => \Auth::user()->name,
                        'billEmail' => \Auth::user()->email,
                        'billPhone' => '000000000',
                        'billSplitPayment' => 0,
                        'billSplitPaymentArgs' => '',
                        'billPaymentChannel' => '0',
                        'billContentEmail' => $billContentEmail,
                        'billChargeToCustomer' => 1,
                        'billExpiryDate' => $billExpiryDate,
                        'billExpiryDays' => $billExpiryDays
                    );
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_POST, 1);
                    curl_setopt($curl, CURLOPT_URL, 'https://sspay.my/index.php/api/createBill');
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $some_data);
                    $result = curl_exec($curl);
                    $info = curl_getinfo($curl);
                    curl_close($curl);
                    $obj = json_decode($result);
                    return redirect('https://sspay.my/' . $obj[0]->BillCode);

                } catch (Exception $e) {
                    return redirect()->route('plan.index')->with('error', __($e->getMessage()));
                }

            } else {
                return redirect()->route('plan.index')->with('error', __('Plan is deleted.'));
            }

    }

    public function getPaymentStatus(Request $request, $planId, $getAmount, $frequency ,$couponCode = null)
    {
        // dd($couponCode ,$frequency , $planId ,$request);
        if ($couponCode != 0) {
            $coupons = Coupon::where('code', strtoupper($couponCode))->where('is_active', '1')->first();
            $request['coupon_id'] = $coupons->id;
        } else {
            $coupons = null;
        }

        $plan = Plan::find($planId);
        $user = Auth::user();
        $getAmount = $plan->{$request->toyyibpay_payment_frequency . '_price'};

        // 1=success, 2=pending, 3=fail
        try {
            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
            if ($request->status_id == 3) {
                $statuses = 'Fail';

                Order::create(
                    [
                        'order_id' => $orderID,
                        'name' => $user->name,
                        'email' => null,
                        'card_number' => null,
                        'card_exp_month' => null,
                        'card_exp_year' => null,
                        'plan_name' => $plan->name,
                        'plan_id' => $plan->id,
                        'price' => $getAmount,
                        'price_currency' => Utility::getAdminCurrency(),
                        'txn_id' => '',
                        'payment_type' => 'Sspay',
                        'payment_status' => $statuses,
                        'receipt' => null,
                        'user_id' => $user->id,
                    ]
                );
                return redirect()->route('plan.index')->with('error', __('Your Transaction is fail please try again'));

            } else if ($request->status_id == 2) {
                $statuses = 'pending';

                Order::create(
                    [
                        'order_id' => $orderID,
                        'name' => $user->name,
                        'email' => null,
                        'card_number' => null,
                        'card_exp_month' => null,
                        'card_exp_year' => null,
                        'plan_name' => $plan->name,
                        'plan_id' => $plan->id,
                        'price' => $getAmount,
                        'price_currency' => Utility::getAdminCurrency(),
                        'txn_id' => '',
                        'payment_type' => 'Sspay',
                        'payment_status' => $statuses,
                        'receipt' => null,
                        'user_id' => $user->id,
                    ]
                );
                return redirect()->route('plan.index')->with('success', __('Your transaction on pending'));
            } else if ($request->status_id == 1) {
                $statuses = 'success';
                Order::create(
                    [
                        'order_id' => $orderID,
                        'name' => $user->name,
                        'email' => null,
                        'card_number' => null,
                        'card_exp_month' => null,
                        'card_exp_year' => null,
                        'plan_name' => $plan->name,
                        'plan_id' => $plan->id,
                        'price' => $getAmount,
                        'price_currency' => Utility::getAdminCurrency(),
                        'txn_id' => '',
                        'payment_type' => 'Sspay',
                        'payment_status' => $statuses,
                        'receipt' => null,
                        'user_id' => $user->id,
                    ]
                );
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
                $assignPlan = $user->assignPlan($plan->id, $frequency);
                $coupons = Coupon::find($request->coupon_id);
                if (!empty($request->coupon_id)) {
                    if (!empty($coupons)) {
                        $userCoupon         = new UserCoupon();
                        $userCoupon->user   = $user->id;
                        $userCoupon->coupon = $coupons->id;
                        $userCoupon->order  = $orderID;
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
                return redirect()->route('plan.index')->with('error', __('Plan is deleted.'));
            }
        } catch (Exception $e) {
            return redirect()->route('plan.index')->with('error', __($e->getMessage()));
        }
    }

    public function invoicepaywithsspay(Request $request){

        $validatorArray = [
            'amount' => 'required',
            'invoice_id' => 'required',
        ];
        $validator      = \Validator::make(
            $request->all(),
            $validatorArray
        )->setAttributeNames(
            ['invoice_id' => 'Invoice']
        );
        if ($validator->fails()) {
            return Utility::error_res($validator->errors()->first());
        }

        $invoiceID = \Crypt::decrypt($request->invoice_id);
        $invoice = Invoice::find($invoiceID);

        if (\Auth::check()) {
            $settings = \DB::table('settings')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('value', 'name')->toArray();
            $user     = \Auth::user();
            $client  =  User::where('created_by', $user->id)->where('type', 'client')->first();
        } else {
            $user = User::where('id', $invoice->created_by)->first();
            $settings = Utility::settingsById($invoice->created_by);
            $client  =  User::where('created_by', $user->id)->where('type', 'client')->first();
        }
        $get_amount = $request->amount;


        if ($invoice)
        {
                if ($get_amount > $invoice->getDue()) {
                    return redirect()->back()->with('error', __('Invalid amount.'));
                } else {
                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                    if (Auth::check()) {
                        $name = Utility::invoiceNumberFormat($settings, $invoice->invoice_id);
                    } else {
                        $user = User::where('id', $invoice->created_by)->first();
                        $name = Utility::invoiceNumberFormat($settings, $invoice->invoice_id);
                    }

                    $this->callBackUrl = route('invoice.sspaypayment', [$invoice->id, $get_amount]);
                    $this->returnUrl = route('invoice.sspay.status', [$invoice->id, $get_amount]);
                }


                try {

                    $Date = date('d-m-Y');
                    $ammount = $get_amount;
                    $billExpiryDays = 3;
                    $billExpiryDate = date('d-m-Y', strtotime($Date . ' + 3 days'));
                    $billContentEmail = "Invoice is successfully paid!";

                    $some_data = array(
                        'userSecretKey' => $this->secretKey,
                        'categoryCode' => $this->categoryCode,
                        'billName' => 'Invoice',
                        'billDescription' => 'Invoice Payment',
                        'billPriceSetting' => 1,
                        'billPayorInfo' => 1,
                        'billAmount' => 100 * $ammount,
                        'billReturnUrl' => $this->returnUrl,
                        'billCallbackUrl' => $this->callBackUrl,
                        'billExternalReferenceNo' => 'AFR341DFI',
                        'billTo' => $user->name,
                        'billEmail' => $user->email,
                        'billPhone' => '000000000',
                        'billSplitPayment' => 0,
                        'billSplitPaymentArgs' => '',
                        'billPaymentChannel' => '0',
                        'billContentEmail' => $billContentEmail,
                        'billChargeToCustomer' => 1,
                        'billExpiryDate' => $billExpiryDate,
                        'billExpiryDays' => $billExpiryDays
                    );
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_POST, 1);
                    curl_setopt($curl, CURLOPT_URL, 'https://sspay.my/index.php/api/createBill');
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $some_data);
                    $result = curl_exec($curl);
                    $info = curl_getinfo($curl);
                    curl_close($curl);
                    $obj = json_decode($result);
                    return redirect('https://sspay.my/' . $obj[0]->BillCode);

                } catch (Exception $e) {
                    return redirect()->back()->with('error', __($e->getMessage()));
                }
            return redirect()->back()->with('error', __('Unknown error occurred.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function getInvoicePaymentStatus(Request $request,$invoice_id, $amount)
    {

        $payment_setting = Utility::set_payment_settings();

        $user             = Auth::user();
        $invoice    = Invoice::find($invoice_id);
        $invoices = Invoice::where('id', $invoice_id)->first();
        if (\Auth::check()) {
            $user = Auth::user();
        } else {
            $user = User::where('id', $invoices->created_by)->first();
        }

        $settings = \DB::table('settings')->where('created_by', '=', $user->creatorId())->get()->pluck('value', 'name');

        if ($request->status_id == 3) {
            if (\Auth::check()) {
                return redirect()->route('invoice.show', $invoice_id)->with('error', __('Your Transaction is failed, please try again'));
            } else {
                return redirect()->route('pay.invoice', \Crypt::encrypt($invoice_id))->with('error', __('Your Transaction is failed, please try again'));
            }
        } else if ($request->status_id == 2) {
            if (\Auth::check()) {
                return redirect()->route('invoice.show', $invoice_id)->with('error', __('Your transaction is pending'));
            } else {
                return redirect()->route('pay.invoice', \Crypt::encrypt($invoice_id))->with('error', __('Your transaction is pending'));
            }
        } else if ($request->status_id == 1) {

            if ($invoice) {
                $order_id = strtoupper(str_replace('.', '', uniqid('', true)));

                $payments = New InvoicePayment;
                $payments->transaction = $order_id;
                $payments->invoice = $invoice->id;
                $payments->amount = $amount;
                $payments->date = date('Y-m-d');
                $payments->payment_method =__('Sspay');
                $payments->payment_type = __('Sspay');
                $payments->notes = __('Invoice') . ' ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id);
                $payments->receipt = '';
                $payments->created_by = $user->creatorId();
                $payments->save();

                if (\Auth::check()) {
                    return redirect()->route('invoice.show', \Crypt::encrypt($invoice_id))->with('success', __('Payment added Successfully'));
                } else {
                    return redirect()->route('pay.invoice', \Crypt::encrypt($invoice_id))->with('success', __('Payment added Successfully'));
                }
            } else {
                if (\Auth::check()) {
                    return redirect()->route('invoice.show', \Crypt::encrypt($invoice_id))->with('error', __('Transaction has been failed! '));
                } else {
                    return redirect()->route('pay.invoice', \Crypt::encrypt($invoice_id))->with('error', __('Transaction has been failed! '));
                }
            }
        }

    }
}

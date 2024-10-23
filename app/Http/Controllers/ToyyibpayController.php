<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\UserCoupon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Utility;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Models\InvoicePayment;
use App\Models\transactions;
use Illuminate\Support\Facades\DB;

class ToyyibpayController extends Controller
{
    public $secretKey, $callBackUrl, $returnUrl, $categoryCode, $is_enabled, $invoiceData;

    public function __construct()
    {
        //     if (\Auth::user()->type == 'company') {
        //         $payment_setting = Utility::getAdminPaymentSetting();
        //     } else {
        //         $payment_setting = Utility::getCompanyPaymentSetting();
        //     }


        $payment_setting = Utility::getCompanyPaymentSetting();
        // dd($payment_setting);
        $this->secretKey = isset($payment_setting['toyyibpay_secret_key']) ? $payment_setting['toyyibpay_secret_key'] : '';

        $this->categoryCode = isset($payment_setting['category_code']) ? $payment_setting['category_code'] : '';
        $this->is_enabled = isset($payment_setting['is_toyyibpay_enabled']) ? $payment_setting['is_toyyibpay_enabled'] : 'off';
    }

    public function index()
    {
        return view('payment');
    }


    public function charge(Request $request)
    {
        try {
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
                $coupon = (empty($request->coupon)) ? "0" : $request->coupon;
                $this->callBackUrl = route('plan.toyyibpay', [$plan->id, $get_amount, $coupon]);
                $this->returnUrl = route('plan.toyyibpay', [$plan->id, $get_amount, $coupon]);



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
                    'billTo' => $user->name,
                    'billEmail' => $user->email,
                    'billPhone' => '00000000000',
                    'billSplitPayment' => 0,
                    'billSplitPaymentArgs' => '',
                    'billPaymentChannel' => '0',
                    'billContentEmail' => $billContentEmail,
                    'billChargeToCustomer' => 1,
                    'billExpiryDate' => $billExpiryDate,
                    'billExpiryDays' => $billExpiryDays
                );
                // dd($some_data);
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_URL, 'https://toyyibpay.com/index.php/api/createBill');
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $some_data);
                $result = curl_exec($curl);
                $info = curl_getinfo($curl);
                curl_close($curl);
                $obj = json_decode($result);
                return redirect('https://toyyibpay.com/' . $obj[0]->BillCode);
            } else {
                return redirect()->route('plans.index')->with('error', __('Plan is deleted.'));
            }
        } catch (Exception $e) {
            return redirect()->route('plans.index')->with('error', __($e->getMessage()));
        }
    }

    public function status(Request $request, $planId, $frequency, $couponCode)
    {
        if ($couponCode != 0) {
            $coupons = Coupon::where('code', strtoupper($couponCode))->where('is_active', '1')->first();
            $request['coupon_id'] = $coupons->id;
        } else {
            $coupons = null;
        }

        $plan = Plan::find($planId);
        $user = Auth::user();
        $getAmount = $plan->{$request->toyyibpay_payment_frequency . '_price'};

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
                        'payment_type' => 'Toyyibpay',
                        'payment_status' => $statuses,
                        'receipt' => null,
                        'user_id' => $user->id,
                    ]
                );
                return redirect()->route('plans.index')->with('error', __('Your Transaction is fail please try again'));
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
                        'payment_type' => 'Toyyibpay',
                        'payment_status' => $statuses,
                        'receipt' => null,
                        'user_id' => $user->id,
                    ]
                );

                return redirect()->route('plans.index')->with('success', __('Your transaction on pending'));
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
                        'payment_type' => 'Toyyibpay',
                        'payment_status' => $statuses,
                        'receipt' => null,
                        'user_id' => $user->id,
                    ]
                );

                $user    = \Auth::user();
                $referral = DB::table('referral_programs')->first();
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

    public function invoicepaywithtoyyibpay(Request $request)
    {
        $validatorArray = [
            'amount' => 'required',
            'invoice_id' => 'required',
        ];
        $validator      = Validator::make(
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

        if ($invoice) {
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

                $this->callBackUrl = route('invoice.toyyibpay.status', [$invoice->id, $get_amount]);
                $this->returnUrl = route('invoice.toyyibpay.status', [$invoice->id, $get_amount]);
            }

            $Date = date('d-m-Y');
            $ammount = $get_amount;
            $billExpiryDays = 3;
            $billExpiryDate = date('d-m-Y', strtotime($Date . ' + 3 days'));
            $billContentEmail = "Thank you for purchasing our product!";
            $some_data = array(
                'userSecretKey' => $this->secretKey,
                'categoryCode' => $this->categoryCode,
                'billName' => $name,
                'billDescription' => $name,
                'billPriceSetting' => 1,
                'billPayorInfo' => 1,
                'billAmount' => 100 * $ammount,
                'billReturnUrl' => $this->returnUrl,
                'billCallbackUrl' => $this->callBackUrl,
                'billExternalReferenceNo' => 'AFR341DFI',
                'billTo' => $user->name,
                'billEmail' => $user->email,
                'billPhone' => '0000000000',
                'billSplitPayment' => 0,
                'billSplitPaymentArgs' => '',
                'billPaymentChannel' => '0',
                'billContentEmail' => $billContentEmail,
                'billChargeToCustomer' => 1,
                'billExpiryDate' => $billExpiryDate,
                'billExpiryDays' => $billExpiryDays
            );
            // dd($invoiceID);
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_URL, 'https://toyyibpay.com/index.php/api/createBill');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $some_data);
            $result = curl_exec($curl);
            $info = curl_getinfo($curl);
            curl_close($curl);
            $obj = json_decode($result);

            return redirect('https://toyyibpay.com/' . $obj[0]->BillCode);

            return redirect()->route('customer.invoice.show', \Crypt::encrypt($invoiceID))->back()->with('error', __('Unknown error occurred'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getInvoicePaymentStatus(Request $request, $invoice_id, $amount)
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

                $payments = new InvoicePayment;
                $payments->transaction = $order_id;
                $payments->invoice = $invoice->id;
                $payments->amount = $amount;
                $payments->date = date('Y-m-d');
                $payments->payment_method = __('Toyyibpay');
                $payments->payment_type = __('Toyyibpay');
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

<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\User;
use App\Models\Utility;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Lahirulhr\PayHere\PayHere;
use Modules\PayHere\Events\PayHerePaymentStatus;
use PhpOffice\PhpSpreadsheet\Calculation\Financial\Coupons;

class PayHereController extends Controller
{
    public function planPayWithPayHere(Request $request)
    {
        $payment_setting = Utility::getAdminPaymentSetting();
        $payhere_merchant_secret_key = isset($payment_setting['payhere_merchant_secret_key']) ? $payment_setting['payhere_merchant_secret_key'] : '';
        $payhere_merchant_id = isset($payment_setting['payhere_merchant_id']) ? $payment_setting['payhere_merchant_id'] : '';
        $payhere_app_id = isset($payment_setting['payhere_app_id']) ? $payment_setting['payhere_app_id'] : '';
        $payhere_app_secret_key = isset($payment_setting['payhere_app_secret_key']) ? $payment_setting['payhere_app_secret_key'] : '';
        $currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'LKR';

        $planID = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $plan = Plan::find($planID);
        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
        $user = Auth::user();
        if ($plan) {
            $price = $plan->price;
            $get_amount = intval($price);

            if (!empty($request->coupon)) {
                $coupons = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();

                if (!empty($coupons)) {
                    $usedCoupun = $coupons->used_coupon();
                    $discount_value = ($plan->price / 100) * $coupons->discount;
                    $get_amount = $plan->price - $discount_value;
                    if ($coupons->limit == $usedCoupun) {

                        return redirect()->back()->with('error', __('This coupon code has expired.'));
                    }

                    $coupon_id = $coupons->id;

                    if ($get_amount < 1) {
                        $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                        $statuses = 'success';
                        if ($coupon_id != '') {


                            $userCoupon         = new UsersCoupons(); //UsersCoupons
                            $userCoupon->user_id   = $user->id;
                            $userCoupon->coupon_id = $coupons->id;
                            $userCoupon->order_id  = $orderID;
                            $userCoupon->save();
                            $usedCoupun = $coupons->used_coupon();
                            if ($coupons->limit <= $usedCoupun) {
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
                        $order->payment_type   = __('PayHere');
                        $order->payment_status = $statuses;
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

            // $call_back = route('plan.get.payhere.status', [
            //     $plan->id,
            // ]);

            try {

                $config = [
                    'payhere.api_endpoint' =>  $payment_setting['PayHere_mode'] === 'sandbox'
                        ? 'https://sandbox.payhere.lk/'
                        : 'https://www.payhere.lk/',
                ];

                $config['payhere.merchant_id'] =  $payment_setting['PayHere_merchant_id'] ?? '';
                $config['payhere.merchant_secret'] =  $payment_setting['PayHere_merchant_secret_key'] ?? '';
                $config['payhere.app_secret'] =  $payment_setting['PayHere_secret_key'] ?? '';
                $config['payhere.app_id'] =  $payment_setting['PayHere_public_id'] ?? '';
                config($config);


                $hash = strtoupper(
                    md5(
                        $payment_setting['PayHere_merchant_id'] .
                            $orderID .
                            number_format($get_amount, 2, '.', '') .
                            'LKR' .
                            strtoupper(md5($payment_setting['PayHere_merchant_secret_key']))
                    )
                );
                $call_back = route('plan.get.payhere.status', [
                    $plan->id,
                    'amount' => $get_amount,
                    'coupon_code' => $request->coupon_code,
                ]);

                $data = [
                    'first_name' => $user->name,
                    'last_name' => '',
                    'email' => $user->email,
                    'phone' => $user->mobile_no ?? '',
                    'address' => 'Main Rd',
                    'city' => 'Anuradhapura',
                    'country' => 'Sri lanka',
                    'order_id' => $orderID,
                    'items' => $plan->name ?? 'Free Plan',
                    'currency' => 'LKR',
                    'amount' => $get_amount,
                    'hash' => $hash,
                    'return_url' =>$call_back,
                ];
                // dd($call_back);
                return PayHere::checkOut()
                    ->data($data)
                    ->successUrl(route('plan.get.payhere.status', [
                        $plan->id,
                        'amount' => $get_amount,
                        'coupon_code' => $request->coupon_code,
                    ]))
                    ->failUrl(route('plan.get.payhere.status', [
                        $plan->id,
                        'amount' => $get_amount,
                        'coupon_code' => $request->coupon_code,
                    ]))
                    ->renderView();
            } catch (\Exception $e) {
                dd($e);
                \Log::debug($e->getMessage());
                return redirect()->route('plans.index')->with('error', $e->getMessage());
            }
        } else {
            
            return redirect()->route('plans.index')->with('error', __('Plan is deleted.'));
        }
    }

    public function planGetPayHereStatus(Request $request, $plan_id)
    {
        // dd($request->all());
        // try {
            if ($request->status == "approved") {
                $user = Auth::user();
                $plan = Plan::find($plan_id);
                $price = $plan->price;
                $get_amount = intval($price);
                $currency = isset($payment_setting['currency']) ? $payment_setting['currency'] : 'LKR';

                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                try {
                    $order = Order::create(
                        [
                            'order_id' => $orderID,
                            'name' => null,
                            'email' => null,
                            'card_number' => null,
                            'card_exp_month' => null,
                            'card_exp_year' => null,
                            'plan_name' => !empty($plan->name) ? $plan->name : 'Basic Package',
                            'plan_id' => $plan->id,
                            'price' => $get_amount,
                            'price_currency' => $currency,
                            'txn_id' => '',
                            'payment_type' => __('PayHere'),
                            'payment_status' => 'succeeded',
                            'receipt' => null,
                            'user_id' => $user->id,
                        ]
                    );
                    $type = 'Subscription';
                    $user = User::find($user->id);
                    $assignPlan = $user->assignPlan($plan->id);
                    $coupons = Coupon::where('code', $request->coupon_code)->first();

                    if (!empty($request->coupon_code)) {
                        if (!empty($coupons)) {
                            $userCoupon = new UsersCoupons();
                            $userCoupon->user_id = $user->id;
                            $userCoupon->coupon_id = $coupons->id;
                            $userCoupon->order_id = $orderID;
                            $userCoupon->save();
                            $usedCoupun = $coupons->used_coupon();
                            if ($coupons->limit <= $usedCoupun) {
                                $coupons->is_active = 0;
                                $coupons->save();
                            }
                        }
                    }

                    if ($assignPlan['is_success']) {
                        Utility::referraltransaction($plan);
                        return redirect()->route('plans.index')->with('success', __('Plan activated Successfully.'));
                    } else {
                        return redirect()->route('plans.index')->with('error', __($assignPlan['error']));
                    }
                } catch (\Exception $e) {
                    return redirect()->route('plans.index')->with('error', __('Transaction has been failed.'));
                }
            } else {
                return redirect()->route('plans.index')->with('error', __('Payment failed'));

            }
        // } catch (\Exception $e) {
        //     dd($e);
        //     return redirect()->route('plans.index')->with('error', $e->getMessage());
        // }
    }
    public function invoicePayWithPayHere(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            ['amount' => 'required|numeric', 'invoice_id' => 'required']
        );

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }
        $invoice_id = \Illuminate\Support\Facades\Crypt::decrypt($request->invoice_id);
        // $invoice_id = $request->invoice_id;
        $invoice = Invoice::find($invoice_id);
        $user = User::where('id', $invoice->created_by)->first();
        $type = "invoice";
        // dd($invoice_id);
        if ($type == 'invoice') {
            $invoice = \App\Models\Invoice::find($invoice_id);
            $user_id = $invoice->created_by;
            $workspace = $invoice->workspace;
            $payment_id = $invoice->id;

        }

        $this->invoiceData = $invoice;
        $company_settings = Utility::getCompanyPaymentSetting($user_id);
        // dd($company_settings);

        $user = User::find($user_id);

        $config = [
            'payhere.api_endpoint' => $company_settings['PayHere_mode'] === 'sandbox'
                ? 'https://sandbox.payhere.lk/'
                : 'https://www.payhere.lk/',
        ];

        $config['payhere.merchant_id'] = $company_settings['PayHere_merchant_id'] ?? '';
        $config['payhere.merchant_secret'] = $company_settings['PayHere_merchant_secret_key'] ?? '';
        $config['payhere.app_secret'] = $company_settings['PayHere_secret_key'] ?? '';
        $config['payhere.app_id'] = $company_settings['PayHere_public_id'] ?? '';
        config($config);

        $get_amount = $request->amount;

        if ($invoice) {
            if ($get_amount > $invoice->getDue()) {
                return redirect()->back()->with('error', __('Invalid amount.'));
            } else {
                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                try {
                    $hash = strtoupper(
                        md5(
                            $company_settings['PayHere_merchant_id'] .
                                $orderID .
                                number_format($get_amount, 2, '.', '') .
                                'LKR' .
                                strtoupper(md5($company_settings['PayHere_merchant_secret_key']))
                        )
                    );

                    $data = [
                        'first_name' => $user->name,
                        'last_name' => '',
                        'email' => $user->email,
                        'phone' => $user->mobile_no ?? '94761234567',
                        'address' => 'Main Rd',
                        'city' => 'Anuradhapura',
                        'country' => 'Sri lanka',
                        'order_id' => $orderID,
                        'items' => 'Invoice',
                        'currency' => 'LKR',
                        'amount' => $get_amount,
                        'hash' => $hash,
                    ];

                    return PayHere::checkOut()
                        ->data($data)
                        ->successUrl(route('invoice.payhere', [$payment_id, $get_amount, $type]))
                        ->failUrl(route('invoice.payhere', [$payment_id, $get_amount, $type]))
                        ->renderView();

                } catch (Exception $e) {
                    if ($request->type == 'invoice') {
                        return redirect()->route('invoice.show', $invoice_id)->with('error', $e->getMessage() ?? 'Something went wrong.');
                    }
                }

                return redirect()->back()->with('error', __('Unknown error occurred'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getInvoicePaymentStatus(Request $request, $invoice_id, $amount, $type)
    {

        if ($type == 'invoice') {
            $invoice = \App\Models\Invoice::find($invoice_id);

            $company_settings = getCompanyAllSetting($invoice->created_by, $invoice->workspace);

            $this->currancy = isset($company_settings['defult_currancy']) ? $company_settings['defult_currancy'] : '$';
            $this->invoiceData = $invoice;

            if ($invoice) {
                if (empty($request->PayerID || empty($request->token))) {
                    return redirect()->route('invoice.show', $invoice_id)->with('error', __('Payment failed'));
                }
                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                try {
                    $invoice_payment = new \App\Models\InvoicePayment();
                    $invoice_payment->invoice_id = $invoice_id;
                    $invoice_payment->date = Date('Y-m-d');
                    $invoice_payment->account_id = 0;
                    $invoice_payment->payment_method = 0;
                    $invoice_payment->amount = $amount;
                    $invoice_payment->order_id = $orderID;
                    $invoice_payment->currency = $this->currancy;
                    $invoice_payment->payment_type = 'PayHere';
                    $invoice_payment->save();

                    $due = $invoice->getDue();
                    if ($due <= 0) {
                        $invoice->status = 4;
                        $invoice->save();
                    } else {
                        $invoice->status = 3;
                        $invoice->save();
                    }
                    if (module_is_active('Account')) {
                        //for customer balance update
                        \Modules\Account\Entities\AccountUtility::updateUserBalance('customer', $invoice->customer_id, $invoice_payment->amount, 'debit');
                    }
                    event(new PayHerePaymentStatus($invoice, $type, $invoice_payment));


                    return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Invoice paid Successfully!'));

                } catch (\Exception $e) {
                    return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', $e->getMessage());
                }
            } else {
                return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Invoice not found.'));
            }

        } elseif ($type == 'salesinvoice') {
            $salesinvoice = \Modules\Sales\Entities\SalesInvoice::find($invoice_id);
            $company_settings = getCompanyAllSetting($salesinvoice->created_by, $salesinvoice->workspace);

            $this->currancy = isset($company_settings['defult_currancy']) ? $company_settings['defult_currancy'] : '$';

            $this->invoiceData = $salesinvoice;
            if ($salesinvoice) {

                if (empty($request->PayerID || empty($request->token))) {
                    return redirect()->route('salesinvoice.show', $invoice_id)->with('error', __('Payment failed'));
                }

                try {
                    $salesinvoice_payment = new \Modules\Sales\Entities\SalesInvoicePayment();
                    $salesinvoice_payment->invoice_id = $invoice_id;
                    $salesinvoice_payment->transaction_id = app('Modules\Sales\Http\Controllers\SalesInvoiceController')->transactionNumber($salesinvoice->created_by);
                    $salesinvoice_payment->date = Date('Y-m-d');
                    $salesinvoice_payment->amount = $amount;
                    $salesinvoice_payment->client_id = 0;
                    $salesinvoice_payment->payment_type = 'PayHere';
                    $salesinvoice_payment->save();
                    $due = $salesinvoice->getDue();
                    if ($due <= 0) {
                        $salesinvoice->status = 3;
                        $salesinvoice->save();
                    } else {
                        $salesinvoice->status = 2;
                        $salesinvoice->save();
                    }
                    event(new PayHerePaymentStatus($salesinvoice, $type, $salesinvoice_payment));


                    return redirect()->route('pay.salesinvoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Sales Invoice paid Successfully!'));

                } catch (\Exception $e) {

                    return redirect()->route('pay.salesinvoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', $e->getMessage());
                }
            } else {

                return redirect()->route('pay.salesinvoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Sales Invoice not found.'));
            }
        } elseif ($type == 'retainer') {
            $retainer = \Modules\Retainer\Entities\Retainer::find($invoice_id);
            $company_settings = getCompanyAllSetting($retainer->created_by, $retainer->workspace);

            $this->currancy = isset($company_settings['defult_currancy']) ? $company_settings['defult_currancy'] : '$';

            $this->invoiceData = $retainer;
            if ($retainer) {
                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                if (empty($request->PayerID || empty($request->token))) {
                    return redirect()->route('retainer.show', $invoice_id)->with('error', __('Payment failed'));
                }

                try {
                    $retainer_payment = new \Modules\Retainer\Entities\RetainerPayment();
                    $retainer_payment->retainer_id = $invoice_id;
                    $retainer_payment->date = Date('Y-m-d');
                    $retainer_payment->account_id = 0;
                    $retainer_payment->payment_method = 0;
                    $retainer_payment->amount = $amount;
                    $retainer_payment->order_id = $orderID;
                    $retainer_payment->currency = $this->currancy;
                    $retainer_payment->payment_type = 'PayHere';
                    $retainer_payment->save();
                    $due = $retainer->getDue();

                    if ($due <= 0) {
                        $retainer->status = 4;
                        $retainer->save();
                    } else {
                        $retainer->status = 2;
                        $retainer->save();
                    }
                    //for customer balance update
                    \Modules\Retainer\Entities\RetainerUtility::updateUserBalance('customer', $retainer->customer_id, $retainer_payment->amount, 'debit');
                    event(new PayHerePaymentStatus($retainer, $type, $retainer_payment));


                    return redirect()->route('pay.retainer', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Retainer paid Successfully!'));

                } catch (\Exception $e) {
                    return redirect()->route('pay.retainer', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', $e->getMessage());
                }
            } else {

                return redirect()->route('pay.retainer', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Retainer not found.'));
            }
        } elseif ($type == 'feesreceipt') {
            $invoice = \Modules\LegalCaseManagement\Entities\FeesReceipt::find($invoice_id);
            $company_settings = getCompanyAllSetting($invoice->created_by, $invoice->workspace);

            $this->currancy = isset($company_settings['defult_currancy']) ? $company_settings['defult_currancy'] : '$';
            $this->invoiceData = $invoice;

            if ($invoice) {

                if (empty($request->PayerID || empty($request->token))) {
                    return redirect()->route('invoice.show', $invoice_id)->with('error', __('Payment failed'));
                }
                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                try {
                    $payments = new \Modules\LegalCaseManagement\Entities\FeesReceiptPayment();
                    $payments->fees_reciept_id = $invoice->id;
                    $payments->amount = $amount;
                    $payments->date = date('Y-m-d');
                    $payments->order_id = $orderID;
                    $payments->currency = $this->currancy;
                    $payments->method = __('PayHere');
                    $payments->save();

                    $payment = \Modules\LegalCaseManagement\Entities\FeesReceiptPayment::where('fees_reciept_id', $invoice->id)->sum('amount');
                    if ($payment >= $invoice->total_amount) {
                        $invoice->status = 'PAID';
                        $invoice->due_amount = 0.00;
                    } else {
                        $invoice->status = 'Partialy Paid';
                        $invoice->due_amount = $invoice->due_amount - $payments->amount;
                    }

                    $invoice->save();
                    $type = 'feereceipt';
                    event(new PayHerePaymentStatus($invoice, $type, $payments));

                    return redirect()->route('pay.fees', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('success', __('Payment added Successfully'));
                } catch (\Exception $e) {
                    return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', $e->getMessage());
                }

            } else {
                return redirect()->route('pay.fees', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('error', __('Retainer not found.'));
            }
        }
    }
}

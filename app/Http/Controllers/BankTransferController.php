<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\User;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\InvoiceBankTransfer;
use Illuminate\Support\Facades\Auth;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\transactions;
use App\Models\Utility;
use App\Models\UserCoupon;
use Illuminate\Support\Facades\DB;

class BankTransferController extends Controller
{
    public $currancy;
    public function store(Request $request)
    {

        //dd($request);
        $planID         = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $plan           = Plan::find($planID);
        $authuser       = Auth::user();
        $coupon_id      = '';
        $user = Auth::user();
        $orderID = time();

        $validator = \Validator::make(
            $request->all(),
            [
                'payment_recipt' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $check = Order::where('plan_id', $plan->id)->where('payment_status', 'Pending')->where('user_id', \Auth::user()->id)->first();

        if ($check) {
            return redirect()->route('plan.index')->with('error', __('You already send Payment request to this plan.'));
        }

        $dir = storage_path() . '/' . 'payment_recipt';
        if (!is_dir($dir)) {
            \File::makeDirectory($dir, $mode = 0777, true, true);
        }
        $file_path = $request->payment_recipt->getClientOriginalName();
        $file = $request->file('payment_recipt');
        $file->move($dir, $file_path);
        $plan    = Plan::find($planID);
        $user    = \Auth::user();
        $referral = DB::table('referral_programs')->first();
        $amount =  ($plan->price * $referral->commission) / 100;
        $referral = DB::table('referral_programs')->first();
        $transactions = transactions::where('uid', $user->id)->get();
        $total = count($transactions);




        if ($plan) {
            $plan->discounted_price = false;
            $price                  = $plan->price;

            if (isset($request->coupon) && !empty($request->coupon)) {
                $request->coupon = trim($request->coupon);
                $coupons         = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                if (!empty($coupons)) {
                    $usedCoupun             = $coupons->used_coupon();
                    $discount_value         = ($price / 100) * $coupons->discount;
                    $plan->discounted_price = $price - $discount_value;
                    // $coupons = Coupon::find($request->coupon);
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

                    if ($usedCoupun >= $coupons->limit) {
                        return Utility::error_res(__('This coupon code has expired.'));
                    }
                    $price = $price - $discount_value;
                    $coupon_id = $coupons->id;
                } else {
                    return Utility::error_res(__('This coupon code is invalid or has expired.'));
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
            $order->price          = isset($coupons) ? $plan->discounted_price : $plan->price;
            $order->price_currency = !empty(Utility::getAdminCurrency()) ? Utility::getAdminCurrency() : 'USD';
            $order->txn_id         = isset($request->TXNID) ? $request->TXNID : '';
            $order->payment_type   = __('Bank Transfer');
            $order->payment_status = 'Pending';
            $order->receipt        = $file_path;
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


            return redirect()->route('plan.index')->with('success', __('Plan payment request send successfully!'));
        }
    }

    public function invoicePayWithBankTransfer(Request $request)
    {
        $amount = $request->amount;
        $orderID = time();
        $settings = Utility::settings();

        $validatorArray = [
            'amount' => 'required',
            'payment_receipt' => 'required',
            'invoice_id' => 'required',
        ];

        $validator      = \Validator::make(
            $request->all(),
            $validatorArray
        )->setAttributeNames(
            ['invoice_id' => 'Invoice']
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $invoice = Invoice::find($request->invoice_id);
        $amount = number_format((float)$request->amount, 2, '.', '');

        if (\Auth::check()) {
            $company_payment = Utility::getCompanyPaymentSetting();
        } else {
            $company_payment = Utility::getNonAuthCompanyPaymentSetting($invoice->created_by);
        }

        $invoice_getdue = number_format((float)$invoice->getDue(), 2, '.', '');
        if ($invoice_getdue < $amount) {
            return Utility::error_res('not correct amount');
        }

        $dir = storage_path() . '/' . 'invoice_payment_receipt';
        if (!is_dir($dir)) {
            \File::makeDirectory($dir, $mode = 0777, true, true);
        }

        $file_path = $request->payment_receipt->getClientOriginalName();
        $file = $request->file('payment_receipt');
        $file->move($dir, $file_path);

        $InvoiceBankTransfer                = new InvoiceBankTransfer();
        $InvoiceBankTransfer->order_id      = $orderID;
        $InvoiceBankTransfer->invoice_id    = $request->invoice_id;
        $InvoiceBankTransfer->amount        = $request->amount;
        $InvoiceBankTransfer->status        = 'Pending';
        $InvoiceBankTransfer->receipt       = $file_path;
        $InvoiceBankTransfer->date          = date("Y-m-d");
        $InvoiceBankTransfer->created_by       = $invoice->created_by;
        $InvoiceBankTransfer->save();

        return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('success', __('Invoice Payment request send successfully!.'));
    }


    public function approve($id, $approval)
    {

        if ($approval == 1) {
            $order = Order::find($id);
            $user = User::find($order->user_id);
            $pn       = Plan::find($order->plan_id);

            $user->plan = $order->plan_id;
            $user->save();

            $order->payment_status = 'Approve';
            $order->save();

            $assignPlan = $user->assignPlan($order->plan_id, $pn->duration);

            return redirect()->back()->with('success', 'Plan Assign Successfully');
        }
    }

    public function reject($id, $approval)
    {
        if ($approval == 0) {
            $order = Order::find($id);

            $order->payment_status = 'Reject';
            $order->save();

            return redirect()->back()->with('error', 'Plan Rejected Successfully');
        }
    }

    // InvoiceBankTransferStatus
    public function InvoiceBankTransferAction($id)
    {
        $invoice_banktransfer = InvoiceBankTransfer::find($id);

        if ($invoice_banktransfer) {
            $invoice = invoice::find($invoice_banktransfer->invoice_id);
            $company_payment_settings = Utility::getCompanyPaymentSetting();
            $user = User::find($invoice_banktransfer->created_by);
            return view('invoice.banktransfer_payment', compact('invoice', 'company_payment_settings', 'invoice_banktransfer', 'user'));
        } else {
            return redirect()->back()->with('error', __('Invoice Not Found.'));
        }
    }


    public function invoiceApprove($id, $approval)
    {
        $invoice_banktransfer = InvoiceBankTransfer::where('id', $id)->first();
        $invoice = invoice::find($invoice_banktransfer->invoice_id);
        $amount = $invoice_banktransfer->amount;
        $user = User::find($invoice_banktransfer->created_by);
        if ($approval == 1) {
            $invoice_banktransfer->update([
                'status' => 'Approve',
            ]);

            $invoice_payment                 = new InvoicePayment();
            $invoice_payment->transaction    = $invoice_banktransfer->order_id;
            $invoice_payment->invoice        = $invoice_banktransfer->invoice_id;
            $invoice_payment->amount         = isset($amount) ? $amount : 0;
            $invoice_payment->date           = date('Y-m-d');
            $invoice_payment->payment_method   = '-';
            $invoice_payment->payment_type   = __('Bank Transfer');
            $invoice_payment->notes          = $user->invoicenumberFormat($invoice->invoice_id);
            $invoice_payment->created_by     = $user->creatorId();
            $invoice_payment->save();

            if ($invoice->getDue() == 0) {
                $invoice->status = 3;
                $invoice->save();
            } else {
                $invoice->status = 2;
                $invoice->save();
            }
            $invoice_banktransfer->delete();
            return redirect()->route('invoice.show', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('success', __('Payment added Successfully'));
        }
    }
    public function invoiceReject($id, $reject)
    {
        $invoice_banktransfer = InvoiceBankTransfer::where('id', $id)->first();
        $invoice = invoice::find($invoice_banktransfer->invoice_id);
        if ($reject == 0) {
            $invoice_banktransfer->update(
                [
                    'status' => 'Reject',
                ]
            );
        }
        return redirect()->route('invoice.show', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('error', __('Invoice Bank Transfer Rejected'));
    }
}

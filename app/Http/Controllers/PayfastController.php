<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\transactions;
use App\Models\UserCoupon;
use App\Models\Utility;
use App\Models\User;
use Doctrine\DBAL\Schema\Index;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class PayfastController extends Controller
{

    public function index(Request $request)
    {
        if (Auth::check()) {
            $payment_setting = Utility::payment_settings();
            $planID = Crypt::decrypt($request->plan_id);
            $plan = Plan::find($planID);

            if ($plan) {
                $plan_amount = $plan->price;
                $order_id = strtoupper(str_replace('.', '', uniqid('', true)));
                $user = Auth::user();
                if ($request->coupon_amount >= 0 && $request->coupon_code != null) {
                    $coupons = Coupon::where('code', $request->coupon_code)->first();
                    if (!empty($coupons)) {
                        $userCoupon = new UserCoupon();
                        $userCoupon->user = $user->id;
                        $userCoupon->coupon = $coupons->id;
                        $userCoupon->order = $order_id;
                        $userCoupon->save();
                        $usedCoupun = $coupons->used_coupon();
                        if ($coupons->limit <= $usedCoupun) {
                            $coupons->is_active = 0;
                            $coupons->save();
                        }
                        $discount_value = ($plan->price / 100) * $coupons->discount;
                        $plan_amount          = $plan->price - $discount_value;
                    }
                }

                $success = Crypt::encrypt([
                    'plan' => $plan->toArray(),
                    'order_id' => $order_id,
                    'plan_amount' => $plan_amount
                ]);

                $data = array(
                    // Merchant details
                    'merchant_id' => !empty($payment_setting['payfast_merchant_id']) ? $payment_setting['payfast_merchant_id'] : '',
                    'merchant_key' => !empty($payment_setting['payfast_merchant_key']) ? $payment_setting['payfast_merchant_key'] : '',
                    'return_url' => route('payfast.payment.success',$success),
                    'cancel_url' => route('plan.index'),
                    'notify_url' => route('plan.index'),
                    // Buyer details
                    'name_first' => $user->name,
                    'name_last' => '',
                    'email_address' => $user->email,
                    // Transaction details
                    'm_payment_id' => $order_id, //Unique payment ID to pass through to notify_url
                    'amount' => number_format(sprintf('%.2f', $plan_amount), 2, '.', ''),
                    'item_name' => $plan->name,
                );

                $passphrase = !empty($payment_setting['payfast_signature']) ? $payment_setting['payfast_signature'] : '';
                $signature = $this->generateSignature($data, $passphrase);
                $data['signature'] = $signature;
                $htmlForm = '';

                foreach ($data as $name => $value) {
                    $htmlForm .= '<input name="' . $name . '" type="hidden" value=\'' . $value . '\' />';
                }

                return response()->json([
                    'success' => true,
                    'inputs' => $htmlForm,
                ]);

            }
        }

    }
    public function generateSignature($data, $passPhrase = null)
    {

        $pfOutput = '';
        foreach ($data as $key => $val) {
            if ($val !== '') {
                $pfOutput .= $key . '=' . urlencode(trim($val)) . '&';
            }
        }
        $getString = substr($pfOutput, 0, -1);
        if ($passPhrase !== null) {
            $getString .= '&passphrase=' . urlencode(trim($passPhrase));
        }
        return md5($getString);
    }

    public function success(Request $request, $success){

        try{
            $user = Auth::user();
            $data = Crypt::decrypt($success);

            $order = new Order();
            $order->order_id = $data['order_id'];
            $order->name = $user->name;
            $order->card_number = '';
            $order->card_exp_month = '';
            $order->card_exp_year = '';
            $order->plan_name = $data['plan']['name'];
            $order->plan_id = $data['plan']['id'];
            $order->price = $data['plan_amount'];
            $order->price_currency = Utility::getAdminCurrency();
            $order->txn_id = $data['order_id'];
            $order->payment_type = __('PayFast');
            $order->payment_status = 'success';
            $order->txn_id = '';
            $order->receipt = '';
            $order->user_id = $user->id;
            $order->save();

            $user    = \Auth::user();
            $referral = DB::table('referral_programs')->first();
           $amount=  ($data['plan_amount'] * $referral->commission) /100;
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
                       'plane_name' => $data['plan']['name'],
                       'plan_price'=> $data['plan_amount'],
                       'commission'=>$referral->commission,
                       'commission_amount'=>$amount,
                       'uid' => $user->id,
                   ]
               );
           }
        }
            $assignPlan = $user->assignPlan($data['plan']['id']);

            if ($assignPlan['is_success']) {
                return redirect()->route('plan.index')->with('success', __('Plan activated Successfully.'));
            } else {
                return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
            }
        }catch(Exception $e){
            return redirect()->route('plan.index')->with('error', __($e));
        }
    }

    public function invoicepaywithpayfast(Request $request)
    {
        $invoice_id = $request->invoice_id;
        $invoice = Invoice::find($invoice_id);
        $user = User::where('id', $invoice->created_by)->first();
        $payment_setting = Utility::invoice_payment_settings($invoice->created_by);
        $get_amount = $request->amount;

        // dd($get_amount);
        if ($invoice) {
            $order_id = strtoupper(str_replace('.', '', uniqid('', true)));
            // dd($order_id);
            if ($get_amount > $invoice->getdue()) {
                return redirect()->back()->with('error', __('Invalid amount.'));
            } else {
                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                $name = isset($user->name) ? $user->name : 'public' . " - " . $invoice->invoice_id;
            }
        }
            $settings  = Utility::settings();
            if (isset($settings['payment_create_notification']) && $settings['payment_create_notification'] == 1) {
                $uArr = [
                    'user_name' => $user->name,
                    'amount'=> $request->amount,
                    'created_by'=> 'by Payfast',
                ];
                Utility::send_slack_msg('new_payment', $uArr);
                }
            if (isset($settings['telegram_payment_create_notification']) && $settings['telegram_payment_create_notification'] == 1) {
                $uArr = [
                    'user_name' => $user->name,
                    'amount'=> $request->amount,
                    'created_by'=> 'by Payfast',
                ];
                Utility::send_telegram_msg('new_payment', $uArr);
                }
            if (isset($settings['twilio_payment_create_notification']) && $settings['twilio_payment_create_notification'] == 1) {
                $uArr = [
                    'user_name' => $user->name,
                    'amount'=> $request->amount,
                    'created_by'=> 'by Payfast',
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

        $success = Crypt::encrypt([
             'order_id' => $order_id,
            'amount' => $get_amount,
            'invoice_id' => $invoice->id
        ]);

        $data = array(
            // Merchant details
            'merchant_id' => !empty($payment_setting['payfast_merchant_id']) ? $payment_setting['payfast_merchant_id'] : '',
            'merchant_key' => !empty($payment_setting['payfast_merchant_key']) ? $payment_setting['payfast_merchant_key'] : '',
            'return_url' => route('invoice.payfast', $success),
            'cancel_url' => route('invoice.show', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id)),
            'notify_url' => route('invoice.show', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id)),
            // Buyer details
            'name_first' => $user->name,
            'name_last' => '',
            'email_address' => $user->email,
            // Transaction details
            'm_payment_id' => $order_id, //Unique payment ID to pass through to notify_url
            'amount' => number_format(sprintf('%.2f', $get_amount), 2, '.', ''),
            'item_name' => 'Invoice',
        );
        $passphrase = !empty($payment_setting['payfast_signature']) ? $payment_setting['payfast_signature'] : '';
        $signature = $this->generateSignature($data, $passphrase);
        $data['signature'] = $signature;

        $htmlForm = '';

        // dd($signature);
        foreach ($data as $name => $value) {
            $htmlForm .= '<input name="' . $name . '" type="hidden" value=\'' . $value . '\' />';
        }


        return response()->json([
            'success' => true,
            'inputs' => $htmlForm,
        ]);
    }


    public function invoicepayfaststatus(Request $request, $success)
    {

        $invoice_id = Crypt::decrypt($success);
        $invoice = Invoice::find($invoice_id['invoice_id']);
        $get_amount = $invoice_id['amount'];
        if(\Auth::check())
        {
            $user=\Auth::user();
        }
        else
        {
            $user= User::where('id',$invoice->created_by)->first();
        }
        $settings = \DB::table('settings')->where('created_by', '=', $user->creatorId())->get()->pluck('value', 'name');

        $order_id = strtoupper(str_replace('.', '', uniqid('', true)));

        if($invoice){
            try {
                $invoice_payment                 = new InvoicePayment();
                $invoice_payment->invoice     = $invoice_id['invoice_id'];
                $invoice_payment->transaction = $order_id;
                // $invoice_payment->client_id = $user->id;
                $invoice_payment->amount         = $get_amount;
                $invoice_payment->date           = date('Y-m-d');
                // $invoice_payment->payment_id     = 0;
                $invoice_payment->notes          = __('Invoice') . ' ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id);;
                $invoice_payment->payment_method = 1;
                $invoice_payment->payment_type   = __('Payfast');
                $invoice_payment->receipt        = __('Invoice') . ' ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id);
                $invoice_payment->created_by = $user->creatorId();
                $invoice_payment->save();

                if ($invoice->getDue() == 0) {
                    $invoice->status = 2;
                    $invoice->save();
                } else {
                    $invoice->status = 3;
                    $invoice->save();
                }

                // dd($get_amount);
                        // }
                    // else {
                if(Auth::check()){
                    return redirect()->route('invoice.show', encrypt($invoice_id['invoice_id']))->with('success', __('Invoice paid Successfully!'));
                }else{
                    return redirect()->route('pay.invoice', encrypt($invoice_id['invoice_id']))->with('success', __('Invoice Paid Successfully'));
                }
                    // }
            } catch (\Exception $e) {
                if(Auth::check()){
                    return redirect()->route('invoice.show', $invoice_id['invoice_id'])->with('error',$e->getMessage());
                }else{
                    return redirect()->route('pay.invoice', encrypt($invoice_id['invoice_id']))->with('success',$e->getMessage());
                }
            }
        } else {
            if(Auth::check()){
                return redirect()->route('invoice.show', $invoice_id['invoice_id'])->with('error',__('Invoice not found.'));
            }else{
                return redirect()->route('pay.invoice', encrypt($invoice_id['invoice_id']))->with('success', __('Invoice not found.'));
            }
        }
    }
}

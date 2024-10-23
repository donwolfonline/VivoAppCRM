<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Utility;
use App\Models\Client;
use App\Models\transactions;
use App\Models\UserCoupon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LivePixel\MercadoPago\MP;

class MercadoPaymentController extends Controller
{
    public $secret_key;
    public $app_id;
    public $is_enabled;

    public function paymentConfig()
    {
        $user=\Auth::user();

        if(\Auth::check())
        {
            $payment_setting = Utility::getAdminPaymentSetting();
            // dd($payment_setting);
        }
        else
        {
            $payment_setting =Utility::getCompanyPaymentSettingWithOutAuth($user->id);
        }
        $this->token = isset($payment_setting['mercado_access_token'])?$payment_setting['mercado_access_token']:'';
        $this->mode = isset($payment_setting['mercado_mode'])?$payment_setting['mercado_mode']:'';
        $this->is_enabled = isset($payment_setting['is_mercado_enabled'])?$payment_setting['is_mercado_enabled']:'off';

        return $this;
    }

    public function planPayWithMercado(Request $request)
    {

        $user=\Auth::user();
        $payment = $this->paymentConfig($user);

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
                            'price_currency' => !empty(Utility::getAdminCurrency()) ? Utility::getAdminCurrency() : 'USD',
                            'txn_id' => '',
                            'payment_type' => 'Mercado',
                            'payment_status' => 'succeeded',
                            'receipt' => null,
                            'user_id' => $authuser->id,
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

            $preference_data = array(
                "items" => array(
                    array(
                        "title" => "Plan : " . $plan->name,
                        "quantity" => 1,
                        "currency_id" => Utility::getAdminCurrency(),
                        "unit_price" => (float)$price,
                    ),
                ),
            );
            \MercadoPago\SDK::setAccessToken($this->token);
            // try
            // {
          // Create a preference object
          $preference = new \MercadoPago\Preference();
          // Create an item in the preference
          $item = new \MercadoPago\Item();
          $item->title = "Plan : " . $plan->name;
          $item->quantity = 1;
          $item->unit_price = (float)$price;
          $preference->items = array($item);

          $success_url = route('plan.mercado.callback',[$request->plan_id,'payment_frequency='.$request->mercado_payment_frequency,'coupon_id='.$coupons_id,'flag'=>'success']);
          $failure_url = route('plan.mercado.callback',[$request->plan_id,'flag'=>'failure']);
          $pending_url = route('plan.mercado.callback',[$request->plan_id,'flag'=>'pending']);

          $preference->back_urls = array(
              "success" => $success_url,
              "failure" => $failure_url,
              "pending" => $pending_url
          );

          $preference->auto_return = "approved";
          $preference->save();

          // Create a customer object
          $payer = new \MercadoPago\Payer();
          // Create payer information
          $payer->name = \Auth::user()->name;
          $payer->email = \Auth::user()->email;
          $payer->address = array(
              "street_name" => ''
          );
          if($this->mode =='live'){
              $redirectUrl = $preference->init_point;
          }else{
              $redirectUrl = $preference->sandbox_init_point;
          }
          return redirect($redirectUrl);

                // return redirect($preference['response']['init_point']);
            // }
            // catch(Exception $e)
            // {
            //     return redirect()->back()->with('error', $e->getMessage());
            // }
            // callback url :  domain.com/plan/mercado

        }
        else
        {
            return redirect()->back()->with('error', 'Plan is deleted.');
        }

    }

    public function getPaymentStatus(Request $request,$plan)
    {

        $planID         = \Illuminate\Support\Facades\Crypt::decrypt($plan);
        $plan           = Plan::find($planID);
        $user = Auth::user();
        $orderID = time();
        if($plan)
        {
            // try
            // {

             if($plan && $request->has('status'))
             {

                 if($request->status == 'approved' && $request->flag =='success')
                 {
                        if(!empty($user->payment_subscription_id) && $user->payment_subscription_id != '')
                        {
                            try
                            {
                                $user->cancel_subscription($user->id);
                            }
                            catch(\Exception $exception)
                            {
                                \Log::debug($exception->getMessage());
                            }
                        }

                        if($request->has('coupon_id') && $request->coupon_id != '')
                        {
                            $coupons = Coupon::find($request->coupon_id);

                            if(!empty($coupons))
                            {
                                $userCoupon            = new UserCoupon();
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
                        $order->price          = $plan->price;
                        $order->price_currency = !empty(Utility::getAdminCurrency()) ? Utility::getAdminCurrency() : 'USD';
                        $order->txn_id         = $request->has('preference_id')?$request->preference_id:'';
                        $order->payment_type   = 'Mercado Pago';
                        $order->payment_status = 'succeeded';
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
                    }else{
                        return redirect()->route('plan.index')->with('error', __('Transaction has been failed! '));
                    }
                }
                else
                {
                    return redirect()->route('plan.index')->with('error', __('Transaction has been failed! '));
                }
            // }
            // catch(\Exception $e)
            // {
            //     return redirect()->route('plan.index')->with('error', __('Plan not found!'));
            // }
        }
    }

    public function invoicePayWithMercado(Request $request)
    {
        try {
            $invoiceID = \Crypt::decrypt($request->invoice_id);

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Invoice Not Found.'));
        }
        $invoiceID = $request->invoice_id;
        $invoiceID = \Crypt::decrypt($invoiceID);
        $invoice   = Invoice::find($invoiceID);

        if(\Auth::check())
        {
            $user=\Auth::user();
        }
        else
        {
            $user= User::find($invoice->created_by);
        }


        $orderID   = strtoupper(str_replace('.', '', uniqid('', true)));


            $payment_setting = Utility::getCompanyPaymentSettingWithOutAuth($invoice->created_by);

            $this->token = isset($payment_setting['mercado_access_token'])?$payment_setting['mercado_access_token']:'';

            $this->mode = isset($payment_setting['mercado_mode'])?$payment_setting['mercado_mode']:'';
            $this->is_enabled = isset($payment_setting['is_mercado_enabled'])?$payment_setting['is_mercado_enabled']:'off';
            $settings = Utility::settingsById($invoice->created_by);


        if($invoice)
        {
            $price = $request->amount;

            if($price > 0)
            {
                $preference_data = array(
                    "items" => array(
                        array(
                            "title" => __('Invoice') . ' ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id),
                            "quantity" => 1,
                            "currency_id" =>  Utility::getValByName('site_currency'),
                            "unit_price" => (float)$price,
                        ),
                    ),
                );

                \MercadoPago\SDK::setAccessToken($this->token);
                // try
                // {
                        $preference = new \MercadoPago\Preference();
                        // Create an item in the preference
                        $item = new \MercadoPago\Item();
                        $item->title = "Invoice : " . $request->invoice_id;
                        $item->quantity = 1;
                        $item->unit_price = (float)$request->amount;
                        $preference->items = array($item);

                        $success_url = route('invoice.mercado',[encrypt($invoice->id),'amount'=>(float)$request->amount,'flag'=>'success']);
                        $failure_url = route('invoice.mercado',[encrypt($invoice->id),'flag'=>'failure']);
                        $pending_url = route('invoice.mercado',[encrypt($invoice->id),'flag'=>'pending']);
                        $preference->back_urls = array(
                            "success" => $success_url,
                            "failure" => $failure_url,
                            "pending" => $pending_url
                        );
                        $preference->auto_return = "approved";
                        $preference->save();

                        // Create a customer object
                        $payer = new \MercadoPago\Payer();
                        // Create payer information
                        $payer->name = $user->name;
                        $payer->email = $user->email;
                        $payer->address = array(
                            "street_name" => ''
                        );

                        if($this->mode =='live'){
                            $redirectUrl = $preference->init_point;
                        }else{
                            $redirectUrl = $preference->sandbox_init_point;
                        }
                        return redirect($redirectUrl);



                // }
                // catch(Exception $e)
                // {
                //     return redirect()->back()->with('error', $e->getMessage());
                // }
                // callback url :  domain.com/plan/mercado
            }
            else
            {
                return redirect()->back()->with('error', 'Enter valid amount.');
            }


        }
        else
        {
            return redirect()->back()->with('error', 'Invoice is deleted.');
        }

    }
    public function getInvoicePaymentStatus(Request $request,$invoice_id)
    {
        // dd($request->    all());
        if(!empty($invoice_id))
        {

            $invoice_id = decrypt($invoice_id);
            $invoice    = Invoice::find($invoice_id);
            $orderID  = strtoupper(str_replace('.', '', uniqid('', true)));
            // $user = \Auth::user();
            if(Auth::check()){
                $user = \Auth::user();
            }else{
                $user= User::where('id',$invoice->created_by)->first();
            }
            $settings  = DB::table('settings')->where('created_by', '=', $user->creatorId())->get()->pluck('value', 'name');

           // $payment   = $this->paymentConfig($user);

            if($invoice && $request->has('status'))
            {
                // try
                // {

                    if($request->status == 'approved' && $request->flag =='success')
                    {
                        // $payments = InvoicePayment::create(
                        //     [
                        //         'invoice' => $invoice->id,
                        //         'date' => date('Y-m-d'),
                        //         'amount' => $request->amount,
                        //         'payment_method' => 1,
                        //         'transaction' => $orderID,
                        //         'payment_type' => __('Mercado Pago'),
                        //         'receipt' => '',
                        //         'notes' => __('Invoice') . ' ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id),
                        //     ]
                        // );

                            $payments = New InvoicePayment;
                            $payments->transaction = $orderID;
                            $payments->invoice = $invoice->id;
                            $payments->amount = $request->amount;
                            $payments->date = date('Y-m-d');
                            $payments->payment_method = __('Mercado Pago');
                            $payments->payment_type = __('Mercado Pago');
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
                        // if(isset($settings['payment_create_notification']) && $settings['payment_create_notification'] ==1){
                        //      $msg = __('New payment of ').$request->amount.' '.__('created for ').$user->name.__(' by Mercado Pago').'.';
                        //     Utility::send_slack_msg($msg);
                        // }
                        if (isset($settings['payment_create_notification']) && $settings['payment_create_notification'] == 1) {
                            $uArr = [
                                'user_name' => $user->name,
                                'amount'=> $request->amount,
                                'created_by'=> 'by Mercado Pago',
                            ];
                            Utility::send_slack_msg('new_payment', $uArr);
                            }
                        if (isset($settings['telegram_payment_create_notification']) && $settings['telegram_payment_create_notification'] == 1) {
                            $uArr = [
                                'user_name' => $user->name,
                                'amount'=> $request->amount,
                                'created_by'=> 'by Mercado Pago',
                            ];
                            Utility::send_telegram_msg('new_payment', $uArr);
                            }
                        if (isset($settings['twilio_invoice_payment_create_notification']) && $settings['twilio_invoice_payment_create_notification'] == 1) {
                            $uArr = [
                                'user_name' => $user->name,
                                'amount'=> $request->amount,
                                'created_by'=> 'by Mercado Pago',
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
                                    return redirect()->route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Invoice paid Successfully!'));
                                }
                                else
                                {
                                    return redirect()->back()->with('error', __('Webhook call failed.'));
                                }
                            }
                        if(\Auth::check())
                        {
                            return redirect()->route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Invoice paid Successfully!'));
                        }
                        else
                        {
                            return redirect()->route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Invoice paid Successfully!'));
                        }
                    }else{

                        if(\Auth::check())
                        {
                            return redirect()->route('invoices.show',$invoice_id)->with('error', __('Transaction fail'));
                        }
                        else
                        {
                            return redirect()->route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('error', __('Transaction fail'));
                        }

                    }
                // }
                // catch(\Exception $e)
                // {
                //     return redirect()->route('invoices.index')->with('error', __('Plan not found!'));
                // }
            }else{
                if(\Auth::check())
                {
                    return redirect()->route('invoices.show',$invoice_id)->with('error', __('Invoice not found.'));
                }
                else
                {
                    return redirect()->route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('error', __('Invoice not found.'));
                }

            }
        }else{
            if(\Auth::check())
            {
                return redirect()->route('invoices.index')->with('error', __('Invoice not found.'));
            }
            else
            {
                return redirect()->route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('success', __('Payment successfully added'));
            }
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\Utility;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{

    public function index()
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'client')
        {
            if(\Auth::user()->type == 'company')
            {
                $payments = Payment::where('created_by', \Auth::user()->creatorId())->with(['clients','paymentMethods'])->get();
            }
            else
            {
                $payments = Payment::where('client', \Auth::user()->id)->with(['clients','paymentMethods'])->get();
            }


            return view('payment.index', compact('payments'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }


    public function create()
    {
        $clients = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'client')->get()->pluck('name', 'id');
        // $clients->prepend('--', 0);
        $paymentMethod = PaymentMethod::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

        return view('payment.create', compact('clients', 'paymentMethod'));
    }


    public function store(Request $request)
    {

        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                    'date' => 'required',
                    'client' => 'required',
                    'amount' => 'required',
                    'payment_method' => 'required',
                    'reference' => 'required',
                ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                // dd($messages);
                return redirect()->back()->with('error', $messages->first());
            }

            $payment                 = new Payment();
            $payment->date           = $request->date;
            $payment->amount         = $request->amount;
            $payment->payment_method = $request->payment_method;
            $payment->client         = $request->client;
            $payment->reference      = $request->reference;
            if($request->receipt)
            {
                // dd('hello');

                // $imageName = 'payment_' . time() . "_" . $request->receipt->getClientOriginalName();
                // $request->receipt->storeAs('uploads/attachment', $imageName);
                // $payment->receipt = $imageName;
                $filenameWithExt = $request->file('receipt')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('receipt')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                $settings = Utility::getStorageSetting();

                $dir        = 'uploads/attachment/';
                $url = '';
                $path = Utility::upload_file($request,'receipt',$filenameWithExt,$dir,[]);
               if($path['flag'] == 1) {
                    $url = $path['url'];
                    $payment->receipt = $url;

                }else {
                    return redirect()->route('payment.index', \Auth::user()->id)->with('error', __($path['msg']));

                }
            }

            $payment->description    = $request->description;
            $payment->created_by     = \Auth::user()->creatorId();
            $payment->save();


            $settings  = Utility::settings();
            $client_namee = Client::where('user_id',$request->client)->first();
            $user_name = User::where('id',$request->client)->first();
            $paymentMethod = PaymentMethod::where('id', '=', $request->payment_method)->first();
            // if(isset($settings['twilio_payment_create_notification']) && $settings['twilio_payment_create_notification'] ==1)
            // {
            //      $message = __('New payment of ').$request->amount.' '.__('created for ').$user_name->name.__(' by ').$paymentMethod->name.'.';
            //      //dd($message);
            //      Utility::send_twilio_msg($client_namee->mobile,$message);
            // }
            if (isset($settings['payment_create_notification']) && $settings['payment_create_notification'] == 1) {
                $uArr = [
                    'user_name' => \Auth::user()->name,
                    'amount'=> $request->amount,
                    'created_by'=> $paymentMethod->name,
                ];
                Utility::send_slack_msg('new_payment', $uArr);
                }

            if (isset($settings['telegram_payment_create_notification']) && $settings['telegram_payment_create_notification'] == 1) {
                $uArr = [
                    'user_name' => \Auth::user()->name,
                    'amount'=> $request->amount,
                    'created_by'=> $paymentMethod->name,
                ];
                Utility::send_telegram_msg('new_payment', $uArr);
                }
            if (isset($settings['twilio_payment_create_notification']) && $settings['twilio_payment_create_notification'] == 1) {
                $uArr = [
                    'user_name' => \Auth::user()->name,
                    'amount'=> $request->amount,
                    'created_by'=> $paymentMethod->name,
                ];
                Utility::send_twilio_msg('new_payment', $uArr);
                }
            //webhook
            $module = "New payment";
            $webhook = Utility::webhookSetting($module);
            if($webhook)
            {
                $parameter = json_encode($payment);

                // 1 parameter is URL , 2  (payment Data) parameter is data , 3 parameter is method
                $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                if($status == true)
                {
                    return redirect()->route('payment.index')->with('success', __('Payment successfully created.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
                }
                else
                {
                    return redirect()->back()->with('error', __('payment Call Failed.'));
                }
            }
            //end webhook
            return redirect()->route('payment.index')->with('success', __('Payment successfully created.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show(Payment $payment)
    {
        //
    }


    public function edit(Payment $payment)
    {
        $clients = User::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'client')->get()->pluck('name', 'id');
        $clients->prepend('--', 0);
        $paymentMethod = PaymentMethod::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

        return view('payment.edit', compact('clients', 'paymentMethod', 'payment'));
    }


    public function update(Request $request, Payment $payment)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'date' => 'required',
                                   'amount' => 'required',
                                   'payment_method' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $payment->date           = $request->date;
            $payment->amount         = $request->amount;
            $payment->payment_method = $request->payment_method;
            $payment->client         = $request->client;
            $payment->reference      = $request->reference;
            $payment->description    = $request->description;
            $payment->save();

            return redirect()->route('payment.index')->with('success', __('Payment successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }


    public function destroy(Payment $payment)
    {
        if(\Auth::user()->type == 'company')
        {
            $payment->delete();

            return redirect()->route('payment.index')->with('success', __('Payment successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function download($image,$extension)
    {
        return Storage::download('uploads/attachment/'.$image.'.'.$extension);

    }
}

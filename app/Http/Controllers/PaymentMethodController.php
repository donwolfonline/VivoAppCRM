<?php

namespace App\Http\Controllers;

use App\Models\InvoicePayment;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        if(\Auth::user()->type == 'company')
        {
            $paymentMethods = PaymentMethod::where('created_by', \Auth::user()->creatorId())->get();

            return view('paymentMethod.index', compact('paymentMethods'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        return view('paymentMethod.create');
    }

    public function store(Request $request)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('paymentMethod.index')->with('error', $messages->first());
            }

            $paymentMethod             = new PaymentMethod();
            $paymentMethod->name       = $request->name;
            $paymentMethod->created_by = \Auth::user()->creatorId();
            $paymentMethod->save();

            return redirect()->route('paymentMethod.index')->with('success', __('Payment method successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show(PaymentMethod $paymentMethod)
    {
        //
    }


    public function edit(PaymentMethod $paymentMethod)
    {
        return view('paymentMethod.edit', compact('paymentMethod'));
    }


    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('paymentMethod.index')->with('error', $messages->first());
            }

            $paymentMethod->name       = $request->name;
            $paymentMethod->created_by = \Auth::user()->creatorId();
            $paymentMethod->save();

            return redirect()->route('paymentMethod.index')->with('success', __('Payment method successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(PaymentMethod $paymentMethod)
    {
        if(\Auth::user()->type == 'company')
        {
            $invoiceData = InvoicePayment::where('payment_method', $paymentMethod->id)->first();
            $paymentData = Payment::where('payment_method', $paymentMethod->id)->first();


            if(!empty($invoiceData) || !empty($paymentData))
            {
                return redirect()->back()->with('error', __('this method is already use so please transfer or delete this method related data.'));
            }

            $paymentMethod->delete();

            return redirect()->route('paymentMethod.index')->with('success', __('Payment method successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\TaxRate;
use Illuminate\Http\Request;

class TaxRateController extends Controller
{
    public function index()
    {
        if(\Auth::user()->type == 'company')
        {
            $taxes = TaxRate::where('created_by', '=', \Auth::user()->creatorId())->get();

            return view('taxRate.index')->with('taxes', $taxes);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        return view('taxRate.create');
    }

    public function store(Request $request)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required|max:20',
                                   'rate' => 'required|numeric',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $tax             = new TaxRate();
            $tax->name       = $request->name;
            $tax->rate       = $request->rate;
            $tax->created_by = \Auth::user()->creatorId();
            $tax->save();

            return redirect()->route('taxRate.index')->with('success', __('Tax rate successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show(TaxRate $taxRate)
    {
        //
    }


    public function edit(TaxRate $taxRate)
    {
        return view('taxRate.edit', compact('taxRate'));
    }


    public function update(Request $request, TaxRate $taxRate)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required|max:20',
                                   'rate' => 'required|numeric',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $taxRate->name = $request->name;
            $taxRate->rate = $request->rate;
            $taxRate->save();

            return redirect()->route('taxRate.index')->with('success', __('Tax rate successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(TaxRate $taxRate)
    {
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}

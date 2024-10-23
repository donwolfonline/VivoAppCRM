<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index()
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
        {
            if(\Auth::user()->type == 'employee')
            {
                $promotions = Promotion::where('created_by', '=', \Auth::user()->creatorId())->where('employee_id', '=', \Auth::user()->id)->get();
            }
            else
            {
                $promotions = Promotion::where('created_by', '=', \Auth::user()->creatorId())->get();
            }

            return view('promotion.index', compact('promotions'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $employees    = User::where('created_by', \Auth::user()->creatorId())->where('type', 'employee')->get()->pluck('name', 'id');

        return view('promotion.create', compact('employees', 'designations'));
    }

    public function store(Request $request)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'employee_id' => 'required',
                                   'designation_id' => 'required',
                                   'promotion_title' => 'required',
                                   'promotion_date' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $promotion                  = new Promotion();
            $promotion->employee_id     = $request->employee_id;
            $promotion->designation_id  = $request->designation_id;
            $promotion->promotion_title = $request->promotion_title;
            $promotion->promotion_date  = $request->promotion_date;
            $promotion->description     = $request->description;
            $promotion->created_by      = \Auth::user()->creatorId();
            $promotion->save();

            return redirect()->route('promotion.index')->with('success', __('Promotion  successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit(Promotion $promotion)
    {
        $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $employees    = User::where('created_by', \Auth::user()->creatorId())->where('type', 'employee')->get()->pluck('name', 'id');

        return view('promotion.edit', compact('promotion', 'employees', 'designations'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'employee_id' => 'required',
                                   'designation_id' => 'required',
                                   'promotion_title' => 'required',
                                   'promotion_date' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $promotion->employee_id     = $request->employee_id;
            $promotion->designation_id  = $request->designation_id;
            $promotion->promotion_title = $request->promotion_title;
            $promotion->promotion_date  = $request->promotion_date;
            $promotion->description     = $request->description;
            $promotion->save();

            return redirect()->route('promotion.index')->with('success', __('Promotion successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Promotion $promotion)
    {
        if(\Auth::user()->type == 'company')
        {
            $promotion->delete();

            return redirect()->route('promotion.index')->with('success', __('Promotion successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}

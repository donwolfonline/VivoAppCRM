<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{

    public function index()
    {
        if(\Auth::user()->type == 'company')
        {
            $leaveTypes = LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();

            return view('leaveType.index', compact('leaveTypes'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        return view('leaveType.create');
    }

    public function store(Request $request)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'title' => 'required',
                                   'days' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $leaveType             = new LeaveType();
            $leaveType->title      = $request->title;
            $leaveType->days       = $request->days;
            $leaveType->created_by = \Auth::user()->creatorId();
            $leaveType->save();

            return redirect()->route('leaveType.index')->with('success', __('LeaveType  successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }


    public function show(LeaveType $leaveType)
    {
        //
    }


    public function edit(LeaveType $leaveType)
    {
        return view('leaveType.edit', compact('leaveType'));
    }


    public function update(Request $request, LeaveType $leaveType)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'title' => 'required',
                                   'days' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $leaveType->title = $request->title;
            $leaveType->days  = $request->days;
            $leaveType->save();

            return redirect()->route('leaveType.index')->with('success', __('LeaveType successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }


    public function destroy(LeaveType $leaveType)
    {
        if(\Auth::user()->type == 'company')
        {
            $data = Leave::where('leave_type', $leaveType->id)->first();
            if(!empty($data))
            {
                return redirect()->back()->with('error', __('this type is already use so please transfer or delete this type related data.'));
            }
            $leaveType->delete();

            return redirect()->route('leaveType.index')->with('success', __('LeaveType successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }


    }
}

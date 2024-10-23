<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index()
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
        {
            if(\Auth::user()->type == 'employee')
            {
                $complaints = Complaint::where('complaint_from', '=', \Auth::user()->id)->with(['complaintFrom','complaintAgainst'])->get();
            }
            else
            {
                $complaints = Complaint::where('created_by', '=', \Auth::user()->creatorId())->with(['complaintFrom','complaintAgainst'])->get();
            }

            return view('complaint.index', compact('complaints'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if(\Auth::user()->type == 'employee')
        {
            $user             = \Auth::user();
            $current_employee = User::where('id', $user->id)->get()->pluck('name', 'id');
            $employees        = User::where('created_by', \Auth::user()->creatorId())->where('id', '!=', $user->id)->where('type', 'employee')->get()->pluck('name', 'id');

        }
        else
        {
            $user             = \Auth::user();
            $current_employee = User::where('id', $user->id)->get()->pluck('name', 'id');
            $employees        = User::where('created_by', \Auth::user()->creatorId())->where('id', '!=', $user->id)->where('type', 'employee')->get()->pluck('name', 'id');
        }


        return view('complaint.create', compact('employees', 'current_employee'));
    }

    public function store(Request $request)
    {
        if(\Auth::user()->type != 'company' || \Auth::user()->type != 'employee')
        {
            if(\Auth::user()->type != 'employee')
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'complaint_from' => 'required',
                                   ]
                );
            }

            $validator = \Validator::make(
                $request->all(), [
                                   'complaint_against' => 'required',
                                   'title' => 'required',
                                   'complaint_date' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $complaint = new Complaint();
            if(\Auth::user()->type == 'employee')
            {
                $emp                       = User::where('id', '=', \Auth::user()->id)->first();
                $complaint->complaint_from = $emp->id;
            }
            else
            {
                $complaint->complaint_from = $request->complaint_from;
            }
            $complaint->complaint_against = $request->complaint_against;
            $complaint->title             = $request->title;
            $complaint->complaint_date    = $request->complaint_date;
            $complaint->description       = $request->description;
            $complaint->created_by        = \Auth::user()->creatorId();
            $complaint->save();


            return redirect()->route('complaint.index')->with('success', __('Complaint  successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(Complaint $complaint)
    {
        return redirect()->route('complaint.index');
    }

    public function edit($complaint)
    {
        $complaint = Complaint::find($complaint);
        if(\Auth::user()->type == 'employee')
        {
            $user             = \Auth::user();
            $current_employee = User::where('id', $user->id)->get()->pluck('name', 'id');
            $employees        = User::where('created_by', \Auth::user()->creatorId())->where('type', 'employee')->where('id', '!=', $user->id)->get()->pluck('name', 'id');
        }
        else
        {
            $user             = \Auth::user();
            $current_employee = User::where('id', $user->id)->get()->pluck('name', 'id');
            $employees        = User::where('created_by', \Auth::user()->creatorId())->where('type', 'employee')->get()->pluck('name', 'id');
        }

        return view('complaint.edit', compact('complaint', 'employees', 'current_employee'));
    }

    public function update(Request $request, Complaint $complaint)
    {
        if(\Auth::user()->type != 'company' || \Auth::user()->type != 'employee')
        {
            if(\Auth::user()->type != 'employee')
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'complaint_from' => 'required',
                                   ]
                );
            }

            $validator = \Validator::make(
                $request->all(), [

                                   'complaint_against' => 'required',
                                   'title' => 'required',
                                   'complaint_date' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            if(\Auth::user()->type == 'employee')
            {
                $emp                       = User::where('id', '=', \Auth::user()->id)->first();
                $complaint->complaint_from = $emp->id;
            }
            else
            {
                $complaint->complaint_from = $request->complaint_from;
            }
            $complaint->complaint_against = $request->complaint_against;
            $complaint->title             = $request->title;
            $complaint->complaint_date    = $request->complaint_date;
            $complaint->description       = $request->description;
            $complaint->save();

            return redirect()->route('complaint.index')->with('success', __('Complaint successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Complaint $complaint)
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
        {
            $complaint->delete();

            return redirect()->route('complaint.index')->with('success', __('Complaint successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}

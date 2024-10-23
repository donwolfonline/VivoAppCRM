<?php

namespace App\Http\Controllers;

use App\Models\Mail\WarningSend;
use App\Models\User;
use App\Models\Warning;
use Illuminate\Http\Request;

class WarningController extends Controller
{
    public function index()
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
        {
            if(\Auth::user()->type == 'employee')
            {
                $warnings = Warning::where('warning_by', '=', \Auth::user()->id)->with(['warningTo','warningBy'])->get();
            }
            else
            {
                $warnings = Warning::where('created_by', '=', \Auth::user()->creatorId())->with(['warningTo','warningBy'])->get();
            }

            return view('warning.index', compact('warnings'));
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
            $employees        = User::where('created_by', \Auth::user()->creatorId())->where('type', '=', 'employee')->where('id', '!=', $user->id)->get()->pluck('name', 'id');
        }
        else
        {
            $user             = \Auth::user();
            $current_employee = User::where('id', $user->id)->get()->pluck('name', 'id');
            $employees        = User::where('created_by', \Auth::user()->creatorId())->where('type', '=', 'employee')->where('id', '!=', $user->id)->get()->pluck('name', 'id');
        }

        return view('warning.create', compact('employees', 'current_employee'));
    }

    public function store(Request $request)
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
        {
            if(\Auth::user()->type != 'employee')
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'warning_by' => 'required',
                                   ]
                );
            }

            $validator = \Validator::make(
                $request->all(), [
                                   'warning_to' => 'required',
                                   'subject' => 'required',
                                   'warning_date' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $warning = new Warning();
            if(\Auth::user()->type == 'employee')
            {
                $emp                 = User::where('user_id', '=', \Auth::user()->id)->first();
                $warning->warning_by = $emp->id;
            }
            else
            {
                $warning->warning_by = $request->warning_by;
            }

            $warning->warning_to   = $request->warning_to;
            $warning->subject      = $request->subject;
            $warning->warning_date = $request->warning_date;
            $warning->description  = $request->description;
            $warning->created_by   = \Auth::user()->creatorId();
            $warning->save();

            return redirect()->route('warning.index')->with('success', __('Warning  successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function edit(Warning $warning)
    {
        if(\Auth::user()->type == 'employee')
        {
            $user             = \Auth::user();
            $current_employee = User::where('id', $user->id)->get()->pluck('name', 'id');
            $employees        = User::where('created_by', \Auth::user()->creatorId())->where('type', '=', 'employee')->where('id', '!=', $user->id)->get()->pluck('name', 'id');
        }
        else
        {
            $user             = \Auth::user();
            $current_employee = User::where('id', $user->id)->get()->pluck('name', 'id');
            $employees        = User::where('created_by', \Auth::user()->creatorId())->where('type', '=', 'employee')->where('id', '!=', $user->id)->get()->pluck('name', 'id');
        }

        return view('warning.edit', compact('warning', 'employees', 'current_employee'));
    }

    public function update(Request $request, Warning $warning)
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
        {
            if(\Auth::user()->type != 'employee')
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'warning_by' => 'required',
                                   ]
                );
            }

            $validator = \Validator::make(
                $request->all(), [
                                   'warning_to' => 'required',
                                   'subject' => 'required',
                                   'warning_date' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            if(\Auth::user()->type == 'employee')
            {
                $emp                 = User::where('id', '=', \Auth::user()->id)->first();
                $warning->warning_by = $emp->id;
            }
            else
            {
                $warning->warning_by = $request->warning_by;
            }

            $warning->warning_to   = $request->warning_to;
            $warning->subject      = $request->subject;
            $warning->warning_date = $request->warning_date;
            $warning->description  = $request->description;
            $warning->save();

            return redirect()->route('warning.index')->with('success', __('Warning successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Warning $warning)
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
        {
            $warning->delete();

            return redirect()->route('warning.index')->with('success', __('Warning successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}

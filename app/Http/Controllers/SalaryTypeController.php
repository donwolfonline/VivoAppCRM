<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalaryType;
use Illuminate\Http\Request;

class SalaryTypeController extends Controller
{

    public function index()
    {
        if(\Auth::user()->type == 'company')
        {
            $types = SalaryType::where('created_by', '=', \Auth::user()->creatorId())->get();

            return view('salaryType.index', compact('types'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        return view('salaryType.create');
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

                return redirect()->back()->with('error', $messages->first());
            }

            $salaryType             = new SalaryType();
            $salaryType->name       = $request->name;
            $salaryType->created_by = \Auth::user()->creatorId();
            $salaryType->save();

            return redirect()->route('salaryType.index')->with(
                'success', 'Salary type successfully created.'
            );
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(SalaryType $salaryType)
    {
        //
    }


    public function edit(SalaryType $salaryType)
    {
        return view('salaryType.edit', compact('salaryType'));
    }


    public function update(Request $request, SalaryType $salaryType)
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

                return redirect()->back()->with('error', $messages->first());
            }

            $salaryType->name       = $request->name;
            $salaryType->created_by = \Auth::user()->creatorId();
            $salaryType->save();

            return redirect()->route('salaryType.index')->with(
                'success', 'Salary type successfully updated.'
            );
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(SalaryType $salaryType)
    {
        if(\Auth::user()->type == 'company')
        {

            $data = Employee::where('salary_type', $salaryType->id)->first();
            if(!empty($data))
            {
                return redirect()->back()->with('error', __('this type is already use so please transfer or delete this type related data.'));
            }

            $salaryType->delete();

            return redirect()->route('salaryType.index')->with(
                'success', 'Salary type successfully deleted.'
            );
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}

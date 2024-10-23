<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    public function index()
    {
        if(\Auth::user()->type == 'company')
        {
            $designations = Designation::where('created_by', '=', \Auth::user()->creatorId())->with('departments')->get();

            return view('designation.index', compact('designations'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        $department = Department::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $department->prepend('Select Department', '');

        return view('designation.create', compact('department'));
    }

    public function store(Request $request)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                                   'department' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $designation             = new Designation();
            $designation->name       = $request->name;
            $designation->department = $request->department;
            $designation->created_by = \Auth::user()->creatorId();
            $designation->save();

            return redirect()->route('designation.index')->with(
                'success', 'Designation successfully created.'
            );
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }


    }


    public function show(Designation $designation)
    {
        //
    }


    public function edit(Designation $designation)
    {
        $department = Department::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $department->prepend('Select Department', '');

        return view('designation.edit', compact('department', 'designation'));
    }


    public function update(Request $request, Designation $designation)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                                   'department' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $designation->name       = $request->name;
            $designation->department = $request->department;
            $designation->save();

            return redirect()->route('designation.index')->with(
                'success', 'Designation successfully updated.'
            );
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }


    }


    public function destroy(Designation $designation)
    {
        if(\Auth::user()->type == 'company')
        {
            $data = Employee::where('designation', $designation->id)->first();
            if(!empty($data))
            {
                return redirect()->back()->with('error', __('this designation is already use so please transfer or delete this designation related data.'));
            }

            $designation->delete();

            return redirect()->route('designation.index')->with(
                'success', 'Designation successfully deleted.'
            );
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}

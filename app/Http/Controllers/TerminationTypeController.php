<?php

namespace App\Http\Controllers;

use App\Models\Termination;
use App\Models\TerminationType;
use Illuminate\Http\Request;

class TerminationTypeController extends Controller
{
    public function index()
    {
        if(\Auth::user()->type == 'company')
        {
            $terminationtypes = TerminationType::where('created_by', '=', \Auth::user()->creatorId())->get();

            return view('terminationtype.index', compact('terminationtypes'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        return view('terminationtype.create');
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

            $terminationtype             = new TerminationType();
            $terminationtype->name       = $request->name;
            $terminationtype->created_by = \Auth::user()->creatorId();
            $terminationtype->save();

            return redirect()->route('termination-type.index')->with('success', __('TerminationType  successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function edit($id)
    {
        $terminationtype = TerminationType::find($id);

        return view('terminationtype.edit', compact('terminationtype'));
    }

    public function update(Request $request, $id)
    {
        if(\Auth::user()->type == 'company')
        {
            $terminationtype = TerminationType::find($id);
            $validator       = \Validator::make(
                $request->all(), [
                                   'name' => 'required|max:20',

                               ]
            );

            $terminationtype->name = $request->name;
            $terminationtype->save();

            return redirect()->route('termination-type.index')->with('success', __('TerminationType successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {

        if(\Auth::user()->type == 'company')
        {
            $terminationtype = TerminationType::find($id);
            $data      = Termination::where('termination_type', $terminationtype->id)->first();
            if(!empty($data))
            {
                return redirect()->back()->with('error', __('this type is already use so please transfer or delete this type related data.'));
            }
            $terminationtype->delete();

            return redirect()->route('termination-type.index')->with('success', __('TerminationType successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}

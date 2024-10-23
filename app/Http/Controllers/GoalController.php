<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Exports\GoalsExport;
use Maatwebsite\Excel\Facades\Excel; 
use Illuminate\Http\Request;

class GoalController extends Controller
{

    public function index()
    {
        if(\Auth::user()->type == 'company')
        {
            $goals = Goal::where('created_by', \Auth::user()->creatorId())->get();

            return view('goal.index', compact('goals'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }


    public function create()
    {
        $types = Goal::$goalType;

        return view('goal.create', compact('types'));
    }


    public function store(Request $request)
    {

        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                                   'amount' => 'required',
                                   'goal_type' => 'required',
                                   'from' => 'required',
                                   'to' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $goal             = new Goal();
            $goal->name       = $request->name;
            $goal->amount     = $request->amount;
            $goal->goal_type  = $request->goal_type;
            $goal->from       = $request->from;
            $goal->to         = $request->to;
            $goal->display    = isset($request->display) ? 1 : 0;
            $goal->created_by = \Auth::user()->creatorId();
            $goal->save();

            return redirect()->route('goal.index')->with('success', __('Goal successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }


    }


    public function show(Goal $goal)
    {
        //
    }


    public function edit(Goal $goal)
    {
        $types = Goal::$goalType;

        return view('goal.edit', compact('types', 'goal'));
    }


    public function update(Request $request, Goal $goal)
    {

        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                                   'amount' => 'required',
                                   'goal_type' => 'required',
                                   'from' => 'required',
                                   'to' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $goal->name      = $request->name;
            $goal->amount    = $request->amount;
            $goal->goal_type = $request->goal_type;
            $goal->from      = $request->from;
            $goal->to        = $request->to;
            $goal->display   = isset($request->display) ? 1 : 0;
            $goal->save();

            return redirect()->route('goal.index')->with('success', __('Goal successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }


    }


    public function destroy(Goal $goal)
    {
        if(\Auth::user()->type == 'company')
        {
            $goal->delete();

            return redirect()->route('goal.index')->with('success', __('Goal successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function export()
    {
        $name = 'goal' . date('Y-m-d i:h:s');
        $data = Excel::download(new GoalsExport(), $name . '.xlsx'); ob_end_clean();

        return $data;
    }
}

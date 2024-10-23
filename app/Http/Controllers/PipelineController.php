<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\Pipeline;
use Illuminate\Http\Request;

class PipelineController extends Controller
{

    public function index()
    {
        if(\Auth::user()->type == 'company')
        {
            $pipelines = Pipeline::where('created_by', '=', \Auth::user()->creatorId())->get();

            return view('pipeline.index', compact('pipelines'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        return view('pipeline.create');
    }


    public function store(Request $request)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required|max:20',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('pipeline.index')->with('error', $messages->first());
            }

            $pipeline             = new Pipeline();
            $pipeline->name       = $request->name;
            $pipeline->created_by = \Auth::user()->creatorId();
            $pipeline->save();

            return redirect()->route('pipeline.index')->with('success', __('Pipeline successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }


    public function show(Pipeline $pipeline)
    {
        //
    }


    public function edit(Pipeline $pipeline)
    {
        return view('pipeline.edit', compact('pipeline'));
    }


    public function update(Request $request, Pipeline $pipeline)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required|max:20',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('pipeline.index')->with('error', $messages->first());
            }

            $pipeline->name       = $request->name;
            $pipeline->created_by = \Auth::user()->creatorId();
            $pipeline->save();

            return redirect()->route('pipeline.index')->with('success', __('Pipeline successfully update.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }


    public function destroy(Pipeline $pipeline)
    {
        if(\Auth::user()->type == 'company')
        {

            $leadData = Lead::where('pipeline_id', $pipeline->id)->first();
            $dealData = Deal::where('pipeline_id', $pipeline->id)->first();

            if(!empty($leadData) || !empty($dealData))
            {
                return redirect()->back()->with('error', __('this pipline is already use so please transfer or delete this pipline related data.'));
            }

            $pipeline->delete();

            return redirect()->route('pipeline.index')->with('success', __('Pipeline successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ProjectStage;
use App\Models\ProjectTask;
use Illuminate\Http\Request;

class ProjectStageController extends Controller
{
    public function index()
    {
        if(\Auth::user()->type == 'company')
        {
            $projectStages = ProjectStage::where('created_by', '=', \Auth::user()->creatorId())->orderBy('order')->get();

            return view('projectStage.index', compact('projectStages'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }


    public function create()
    {
        return view('projectStage.create');
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

                return redirect()->route('projectStage.index')->with('error', $messages->first());
            }
            $all_stage         = ProjectStage::where('created_by', \Auth::user()->creatorId())->orderBy('id', 'DESC')->first();
            $stage             = new ProjectStage();
            $stage->name       = $request->name;
            $stage->color      = $request->color;
            $stage->created_by = \Auth::user()->creatorId();
            $stage->order      = (!empty($all_stage) ? ($all_stage->order + 1) : 0);
            $stage->save();

            return redirect()->route('projectStage.index')->with('success', __('Project stage successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show(ProjectStage $projectStage)
    {
        //
    }


    public function edit(ProjectStage $projectStage)
    {
        return view('projectStage.edit', compact('projectStage'));
    }


    public function update(Request $request, ProjectStage $projectStage)
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

                return redirect()->route('projectStage.index')->with('error', $messages->first());
            }
            $projectStage->color = $request->color;
            $projectStage->name  = $request->name;
            $projectStage->save();

            return redirect()->route('projectStage.index')->with('success', __('Project stage successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(ProjectStage $projectStage)
    {
        if(\Auth::user()->type == 'company')
        {
            $checkStage = ProjectTask::where('stage', '=', $projectStage->id)->get()->toArray();
            if(empty($checkStage))
            {
                $projectStage->delete();

                return redirect()->back()->with('success', __('Project stage successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Project task already assign this stage , so please remove or move task to other project stage.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function order(Request $request)
    {
        $post = $request->all();
        foreach($post['order'] as $key => $item)
        {
            $stage        = ProjectStage::where('id', '=', $item)->first();
            $stage->order = $key;
            $stage->save();
        }
    }
}

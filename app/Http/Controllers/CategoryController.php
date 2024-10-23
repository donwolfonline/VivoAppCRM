<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Estimate;
use App\Models\Item;
use App\Models\Project;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        if(\Auth::user()->type == 'company')
        {
            $categories = Category::where('created_by', '=', \Auth::user()->creatorId())->get();

            return view('category.index', compact('categories'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        $types = Category::$categoryType;

        return view('category.create', compact('types'));
    }

    public function store(Request $request)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                                   'type' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $category             = new Category();
            $category->name       = $request->name;
            $category->type       = $request->type;
            $category->created_by = \Auth::user()->creatorId();
            $category->save();

            return redirect()->route('category.index')->with('success', __('Category successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show(Category $category)
    {
        //
    }


    public function edit(Category $category)
    {
        $types = Category::$categoryType;

        return view('category.edit', compact('category', 'types'));
    }


    public function update(Request $request, Category $category)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required|max:20',
                                   'type' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $category->name = $request->name;
            $category->type = $request->type;
            $category->save();

            return redirect()->route('category.index')->with('success', __('Category successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(Category $category)
    {
        if(\Auth::user()->type == 'company')
        {
            if($category->type == 0)
            {
                $data = Item::where('category', $category->id)->first();
            }
            elseif($category->type == 1)
            {
                $data = Estimate::where('category', $category->id)->first();
            }
            else
            {
                $data = Project::where('category', $category->id)->first();
            }

            if(!empty($data))
            {
                return redirect()->back()->with('error', __('this category is already use so please transfer or delete this category related data.'));
            }
            $category->delete();

            return redirect()->route('category.index')->with('success', __('Category successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}

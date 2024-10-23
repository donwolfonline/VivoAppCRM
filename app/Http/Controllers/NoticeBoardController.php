<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\NoticeBoard;
use App\Models\User;
use App\Models\UserDefualtView;
use Illuminate\Http\Request;

class NoticeBoardController extends Controller
{

    public function index(Request $request)
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee' || \Auth::user()->type == 'client')
        {
            if(\Auth::user()->type == 'client')
            {
                $noticeBoards = NoticeBoard::where('created_by', \Auth::user()->creatorId())->where('type', 'client');
            }
            elseif(\Auth::user()->type == 'employee')
            {
                $noticeBoards = NoticeBoard::where('created_by', \Auth::user()->creatorId())->where('type', 'employee');
            }
            else
            {
                $noticeBoards = NoticeBoard::where('created_by', \Auth::user()->creatorId());
            }
            if(!empty($request->type))
            {
                $noticeBoards->where('type', $request->type);
            }
            $noticeBoards = $noticeBoards->with('departments')->get();

            $defualtView         = new UserDefualtView();
            $defualtView->route  = \Request::route()->getName();
            $defualtView->module = 'notice board';
            $defualtView->view   = 'list';
            User::userDefualtView($defualtView);

            return view('noticeBoard.index', compact('noticeBoards'));
        } else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }


    public function create()
    {
        $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $departments->prepend('All', 0);

        return view('noticeBoard.create', compact('departments'));
    }


    public function store(Request $request)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'heading' => 'required',
                                   'type' => 'required',
                                   'notice_detail' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $noticeBoard                = new NoticeBoard();
            $noticeBoard->heading       = $request->heading;
            $noticeBoard->type          = $request->type;
            $noticeBoard->notice_detail = $request->notice_detail;
            $noticeBoard->department    = $request->department;
            $noticeBoard->created_by    = \Auth::user()->creatorId();
            $noticeBoard->save();

            return redirect()->route('noticeBoard.index')->with('success', __('Notice Board successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }


    }


    public function show(NoticeBoard $noticeBoard)
    {
        //
    }


    public function edit(NoticeBoard $noticeBoard)
    {
        $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $departments->prepend('All', 0);

        return view('noticeBoard.edit', compact('departments', 'noticeBoard'));
    }


    public function update(Request $request, NoticeBoard $noticeBoard)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'heading' => 'required',
                                   'type' => 'required',
                                   'notice_detail' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $noticeBoard->heading       = $request->heading;
            $noticeBoard->type          = $request->type;
            $noticeBoard->notice_detail = $request->notice_detail;
            $noticeBoard->department    = $request->department;
            $noticeBoard->save();

            return redirect()->route('noticeBoard.index')->with('success', __('Notice Board successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(NoticeBoard $noticeBoard)
    {
        if(\Auth::user()->type == 'company')
        {
            $noticeBoard->delete();

            return redirect()->route('noticeBoard.index')->with('success', __('Notice Board successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function grid(Request $request)
    {
        // dd(\Auth::user());
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee' || \Auth::user()->type == 'client')
        {
            if(\Auth::user()->type == 'client')
            {
                $noticeBoards = NoticeBoard::where('created_by', \Auth::user()->creatorId())->where('type', 'client');
            }
            elseif(\Auth::user()->type == 'employee')
            {
                $noticeBoards = NoticeBoard::where('created_by', \Auth::user()->creatorId())->where('type', 'employee');
            }
            else
            {
                $noticeBoards = NoticeBoard::where('created_by', \Auth::user()->creatorId());
            }
            if(!empty($request->type))
            {
                $noticeBoards->where('type', $request->type);
            }
            $noticeBoards = $noticeBoards->get();
            $defualtView         = new UserDefualtView();
            $defualtView->route  = \Request::route()->getName();
            $defualtView->module = 'notice board';
            $defualtView->view   = 'grid';
            User::userDefualtView($defualtView);
            return view('noticeBoard.grid', compact('noticeBoards'));
        } else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }


    }
}

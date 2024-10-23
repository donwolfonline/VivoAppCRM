<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Utility  ;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class NoteController extends Controller
{

    public function index()
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee' || \Auth::user()->type == 'client')
        {
            if(\Auth::user()->type == 'employee' || \Auth::user()->type == 'client')
            {
                $notes = Note::where('created_by', \Auth::user()->id)->get();
            }
            else
            {
                $notes = Note::where('created_by', \Auth::user()->creatorId())->get();
            }

            return view('note.index', compact('notes'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        return view('note.create');
    }


    public function store(Request $request)
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee' || \Auth::user()->type == 'client')
        {

            $validator = \Validator::make(
                $request->all(), [
                                   'title' => 'required',
                                   'description' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $note              = new Note();
            $note->title       = $request->title;
            $note->description = $request->description;
            if(!empty($request->file))
            {
                // $fileName = time() . "_" . $request->file->getClientOriginalName();
                // $request->file->storeAs('uploads/notes', $fileName);
                // $note->file = $fileName;
                    $filenameWithExt = $request->file('file')->getClientOriginalName();
                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('file')->getClientOriginalExtension();
                    ;
                    $fileNameToStore = $filename . '_' . date('m') . '.' . $extension;
                    // dd($fileNameToStore);
                    $settings = Utility::getStorageSetting();

                    $dir        = 'uploads/notes/';
                    $url = '';
                    $path = Utility::upload_file($request,'file',$fileNameToStore,$dir,[]);

                    if($path['flag'] == 1){
                        $url = $path['url'];
                        $note->file = $fileNameToStore;
                    
                    }else{
                        return redirect()->route('note.index', \Auth::user()->id)->with('error', __($path['msg']));
                    }
            }
            if(\Auth::user()->type == 'employee' || \Auth::user()->type == 'client')    
            {
                $note->created_by = \Auth::user()->id;
            }
            else
            {
                $note->created_by = \Auth::user()->creatorId();
            }

            $note->save();

            return redirect()->route('note.index')->with('success', __('Note successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function show(Note $note)
    {
        //
    }


    public function edit(Note $note)
    {
        return view('note.edit', compact('note'));
    }


    public function update(Request $request, Note $note)
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee' || \Auth::user()->type == 'client')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'title' => 'required',
                                   'description' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $note->title       = $request->title;
            $note->description = $request->description;
            if(!empty($request->file))
            {
                

                $filenameWithExt = $request->file('file')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('file')->getClientOriginalExtension();
              
                $fileNameToStore = $filename . '_' . date('m') . '.' . $extension;
                $settings = Utility::getStorageSetting();
                if($note->file)
                {
                    \File::delete(storage_path('uploads/notes/' . $note->file));
                }
                $dir        = 'uploads/notes/';
                $url = '';
                $path = Utility::upload_file($request,'file',$fileNameToStore,$dir,[]);

                if($path['flag'] == 1){
                    $url = $path['url'];
                    $note->file = $fileNameToStore;
                   
                }else{
                    return redirect()->route('note.index', \Auth::user()->id)->with('error', __($path['msg']));
                }
            }

            $note->save();

            return redirect()->route('note.index')->with('success', __('Note successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(Note $note)
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee' || \Auth::user()->type == 'client')
        {
            $note->delete();
            if($note->file)
            {
                \File::delete(storage_path('uploads/notes/' . $note->file));
            }

            return redirect()->route('note.index')->with('success', __('Note successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }
    
    public function download($image,$extension)
    {
        return Storage::download('uploads/notes/'.$image.'.'.$extension);
        
    }
}

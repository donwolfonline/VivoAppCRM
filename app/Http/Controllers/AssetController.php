<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Imports\AssetImport;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index()
    {
        $assets = Asset::where('created_by', '=', \Auth::user()->creatorId())->get();

        return view('assets.index', compact('assets'));
    }


    public function create()
    {
        return view('assets.create');
    }


    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(), [
                               'name' => 'required',
                               'purchase_date' => 'required',
                               'supported_date' => 'required',
                               'amount' => 'required',
                           ]
        );
        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $assets                 = new Asset();
        $assets->name           = $request->name;
        $assets->purchase_date  = $request->purchase_date;
        $assets->supported_date = $request->supported_date;
        $assets->amount         = $request->amount;
        $assets->description    = $request->description;
        $assets->created_by     = \Auth::user()->creatorId();
        $assets->save();

        return redirect()->route('account-assets.index')->with('success', __('Assets successfully created.'));
    }

    public function show(Asset $asset)
    {
        //
    }


    public function edit($id)
    {
        $asset = Asset::find($id);

        return view('assets.edit', compact('asset'));
    }


    public function update(Request $request, $id)
    {
        $asset = Asset::find($id);
        if($asset->created_by == \Auth::user()->creatorId())
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                                   'purchase_date' => 'required',
                                   'supported_date' => 'required',
                                   'amount' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $asset->name           = $request->name;
            $asset->purchase_date  = $request->purchase_date;
            $asset->supported_date = $request->supported_date;
            $asset->amount         = $request->amount;
            $asset->description    = $request->description;
            $asset->save();

            return redirect()->route('account-assets.index')->with('success', __('Assets successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy($id)
    {
        $asset = Asset::find($id);
        if($asset->created_by == \Auth::user()->creatorId())
        {
            $asset->delete();

            return redirect()->route('account-assets.index')->with('success', __('Assets successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function importFile()
    {
        return view('assets.import');
    }

    public function import(Request $request)
    {

        $rules = [
            'file' => 'required|mimes:csv,txt',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }
     

        $assets = (new AssetImport())->toArray(request()->file('file'))[0];
        
        $totalassets = count($assets) - 1;
        $errorArray    = [];
        for($i = 1; $i <= count($assets) - 1; $i++)
        {
            $asset = $assets[$i];
            
            $customerByEmail = Asset::where('name', $asset[1])->first();
            if(!empty($customerByEmail))
            {  
                 $assetsData = $customerByEmail;
            }
            else
            {
                $assetsData = new Asset();
            }
            
             $assetsData->name             = $asset[0];
            $assetsData->purchase_date    = $asset[1];
            $assetsData->supported_date   = $asset[2];
            $assetsData->amount           = $asset[3];
            $assetsData->description      = $asset[4];
            $assetsData->created_by       = \Auth::user()->creatorId();
//            dd($assetsData);

            if(empty($assetsData))
            {
                $errorArray[] = $assetsData;
            }
            else
            {
                $assetsData->save();
            }
            
        }
        $errorRecord = [];
        if(empty($errorArray))
        {
            $data['status'] = 'success';
            $data['msg']    = __('Record successfully imported');
        }
        else
        {
            $data['status'] = 'error';
            $data['msg']    = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalassets . ' ' . 'record');


            foreach($errorArray as $errorData)
            {

                $errorRecord[] = implode(',', $errorData);

            }

            \Session::put('errorArray', $errorRecord);
        }

        return redirect()->back()->with($data['status'], $data['msg']);
    }
}

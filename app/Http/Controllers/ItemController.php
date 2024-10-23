<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\TaxRate;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserDefualtView;
use Illuminate\Http\Request;
use App\Exports\ItemExport;
use App\Imports\ItemImport;
use Maatwebsite\Excel\Facades\Excel;

class ItemController extends Controller
{

    public function index()
    {
        if(\Auth::user()->type == 'company')
        {
            $items               = Item::where('created_by', '=', \Auth::user()->creatorId())->with(['categories','taxes','units'])->get();
            $defualtView         = new UserDefualtView();
            $defualtView->route  = \Request::route()->getName();
            $defualtView->module = 'item';
            $defualtView->view   = 'list';
            User::userDefualtView($defualtView);
            $getTaxData          = Item::getTaxData();

            return view('item.index', compact('items','getTaxData'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        $category = Category::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 0)->get()->pluck('name', 'id');
        $category->prepend('Select Category');
        $unit = Unit::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $unit->prepend('Select Unit', '');
        $tax = TaxRate::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');


        return view('item.create', compact('category', 'unit', 'tax'));
    }


    public function store(Request $request)
    {

        if(\Auth::user()->type == 'company')
        {
            $rules = [
                'name' => 'required',
                'sku' => 'required',
                'sale_price' => 'required|numeric',
                'purchase_price' => 'required|numeric',
                'category' => 'required',
                'unit' => 'required',
                'type' => 'required',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('item.index')->with('error', $messages->first());
            }

            $item                 = new Item();
            $item->name           = $request->name;
            $item->description    = $request->description;
            $item->sku            = $request->sku;
            $item->sale_price     = $request->sale_price;
            $item->purchase_price = $request->purchase_price;
            $item->tax            = !empty($request->tax) ? implode(',', $request->tax) : '';
            $item->unit           = $request->unit;
            $item->type           = $request->type;
            $item->category       = $request->category;
            $item->quantity       = $request->quantity;
            $item->created_by     = \Auth::user()->creatorId();
            $item->save();

            return redirect()->route('item.index')->with('success', __('Item successfully created.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }


    public function show(Item $item)
    {
        //
    }


    public function edit(Item $item)
    {
        $category = Category::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 0)->get()->pluck('name', 'id');
        $category->prepend('Select Category');
        $unit = Unit::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $unit->prepend('Select Unit');
        $tax = TaxRate::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

        $item->tax = explode(',', $item->tax);

        return view('item.edit', compact('category', 'unit', 'tax', 'item'));
    }


    public function update(Request $request, Item $item)
    {
        if(\Auth::user()->type == 'company')
        {
            $rules = [
                'name' => 'required',
                'sku' => 'required',
                'sale_price' => 'required|numeric',
                'purchase_price' => 'required|numeric',
                'category' => 'required',
                'unit' => 'required',
                'type' => 'required',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('item.index')->with('error', $messages->first());
            }


            $item->name           = $request->name;
            $item->description    = $request->description;
            $item->sku            = $request->sku;
            $item->sale_price     = $request->sale_price;
            $item->purchase_price = $request->purchase_price;
            $item->tax            = !empty($request->tax) ? implode(',', $request->tax) : '';
            $item->unit           = $request->unit;
            $item->type           = $request->type;
            $item->category       = $request->category;
            $item->quantity       = $request->quantity;
            $item->save();

            // if($request->quantity ){
            //     DB::table('items')->decrement('quantity', $order_products->qty); 
            // }

            return redirect()->route('item.index')->with('success', __('Item successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }


    public function destroy(Item $item)
    {
        if(\Auth::user()->type == 'company')
        {
            $item->delete();

            return redirect()->route('item.index')->with('success', __('Item successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function grid()
    {
        if(\Auth::user()->type == 'company')
        {
            $items = Item::where('created_by', '=', \Auth::user()->creatorId())->with('categories')->get();

            $defualtView         = new UserDefualtView();
            $defualtView->route  = \Request::route()->getName();
            $defualtView->module = 'item';
            $defualtView->view   = 'grid';
            User::userDefualtView($defualtView);
            $getTaxData   = Item::getTaxData();

            return view('item.grid', compact('items','getTaxData'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function export()
    {
        $name = 'item' . date('Y-m-d i:h:s');
        $data = Excel::download(new ItemExport(), $name . '.xlsx'); ob_end_clean();

        return $data;
    }


    public function importFile()
    {
        return view('item.import');
    }

    public function import(Request $request)
    {

        $rules = [
            'file' =>  'required|mimes:csv,txt',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }
     

        $customers = (new ItemImport())->toArray(request()->file('file'))[0];
        
        $totalitem = count($customers) - 1;
        $errorArray    = [];
        for($i = 1; $i <= count($customers) - 1; $i++)
        {
            $customer = $customers[$i];

            $customerByEmail = Item::where('name', $customer[1])->first();
            if(!empty($customerByEmail))
            {
                $customerData = $customerByEmail;
            }
            else
            {
                $customerData = new Item();
            }
            
            $customerData->name           = $customer[0];
            $customerData->sku            = $customer[1];
            $customerData->sale_price	  = $customer[2];
            $customerData->purchase_price = $customer[3];
            $customerData->quantity        = $customer[4];
            $customerData->tax             = 1;
            $customerData->category            = 1;
            $customerData->unit             = 1;
            $customerData->type            = $customer[8];
            $customerData->description     = $customer[9];
            $customerData->created_by       = \Auth::user()->creatorId();

            if(empty($customerData))
            {
                $errorArray[] = $customerData;
            }
            else
            {
                $customerData->save();
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
            $data['msg']    = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalitem . ' ' . 'record');


            foreach($errorArray as $errorData)
            {

                $errorRecord[] = implode(',', $errorData);

            }

            \Session::put('errorArray', $errorRecord);
        }

        return redirect()->back()->with($data['status'], $data['msg']);
    }

}

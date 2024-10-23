<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemStock;
use App\Models\Utility;
use Illuminate\Http\Request;

class ItemStockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
            $Items = Item::where('created_by', '=', \Auth::user()->creatorId())->get();
            return view('itemstock.index', compact('Items'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {


    }

    public function show(ItemStock $productStock)
    {
        //
    }

   
    public function edit($id)
    {
        $Item = Item::find($id);
            if ($Item->created_by == \Auth::user()->creatorId())
            {
                return view('itemstock.edit', compact( 'Item'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductStock  $productStock
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
            $Item = Item::find($id);
            if($request->quantity_type == 'Add')
            {
                $total=$Item->quantity + $request->quantity;

            }
            else {
                $total=$Item->quantity - $request->quantity;
            }

            if ($Item->created_by == \Auth::user()->creatorId())
            {
                $Item->quantity        = $total;
                $Item->created_by     = \Auth::user()->creatorId();
                $Item->save();

                //Product Stock Report
                $type='manually';
                $type_id = 0;
                $description=$request->quantity.'  '.__('quantity added by manually');
                Utility::addProductStock( $Item->id,$request->quantity,$type,$description,$type_id);


                return redirect()->route('itemstock.index')->with('success', __('Product quantity updated manually.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductStock  $productStock
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductStock $productStock)
    {
        //
    }
}

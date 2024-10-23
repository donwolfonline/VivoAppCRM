<?php

namespace App\Exports;

use App\Models\Category;
use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ItemExport implements FromCollection , WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = Item::where('created_by', '=', \Auth::user()->creatorId())->get();
        foreach($data as $k => $item)
        {
           if($item->unit == 0 ){
                $unit_id ='0';
           }
           else{
            $unit_id    = Item::unit($item->unit);
           }

           if($item->category == 0){
            $category = '0';
           }
           else{
                $category   = Item::category($item->category);
           }
           if($item->tax == 0){
                 $taxes ='0';
           }
           else{
                $taxes      = Item::taxs($item->tax);
           }

            unset($item->id,$item->created_by,$item->created_at,$item->updated_at);
            $data[$k]["unit"]            = $unit_id;
            $data[$k]["category"]        = $category;
            $data[$k]["tax"]             = $taxes;

        }
        return $data;
    }

    public function headings(): array
    {
        return [
            "Name",
            "SKU",
            "Sale Price",
            "Purchase Price",
            "Quantity",
            "Tax",
            "Category",
            "Unit",
            "Type",
            "Description",
        ];
    }

}

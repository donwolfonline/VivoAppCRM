<?php

namespace App\Exports;

use App\Models\Export;
use App\Models\Estimate;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EstimateExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        
        $data = Estimate::where('created_by', '=', \Auth::user()->creatorId())->get();
   


       

        foreach($data as $k => $estimates)
        {
            unset($estimates->send_date,$estimates->discount_apply,$estimates->is_convert,$estimates->created_by,$estimates->created_at,$estimates->updated_at);
            $data[$k]["id"]            = $estimates->id;
            $data[$k]["estimate"]         = $estimates->estimate;
            $data[$k]["client"]      = $estimates->client;
            $data[$k]["issue_date"]      = $estimates->issue_date;
            $data[$k]["expiry_date"]      = $estimates->expiry_date;
            $data[$k]["category"]     = $estimates->category;

           
        }  

        return $data;
    }

    public function headings(): array
    {
        return [
            "id",
            "estimate",
            "client",
            "issue_date",
            "expiry_date",
            "category",
          
        ];
    }
}

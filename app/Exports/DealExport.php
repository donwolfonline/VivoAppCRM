<?php

namespace App\Exports;

use App\Models\Export;
use App\Models\Deal;
use App\Models\DealStage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DealExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // $data = ProjectTask::where('project_id' ,$this->id)->get();
        // $data = Deal::where('created_by', \Auth::user()->id)->get();
        // $data = Deal::where('assign_to', '=', \Auth::user()->creatorId())->get();
        $data = Deal::where('created_by', '=', \Auth::user()->creatorId())->get();
        
        // dd($data->toArray());
        foreach($data as $k => $deals)
        {
            unset($deals->sources,$deals->products, $deals->items,$deals->notes,$deals->labels,$deals->order,$deals->is_active,$deals->created_at,$deals->created_by,$deals->is_converted,$deals->date,$deals->	updated_at);
            $data[$k]["id"]            = $deals->id;
            $data[$k]["name"]         = $deals->name;
            $data[$k]["price"]      = $deals->price;
            $data[$k]["phone_no"]      = $deals->phone_no;
            $data[$k]["pipeline_id"]   = !empty($deals->pipeline) ? $deals->pipeline->name : '';
            $data[$k]["stage_id"]       = !empty($deals->stage) ? $deals->stage->name : '';
            $data[$k]["status"]       = $deals->status;
                   
        }  

        return $data;
    }

    public function headings(): array
    {
        return [
            "id",
            "name",
            "price",
            "phone_no",
            "pipeline",
            "stage",
            "status",
          
        ];
    }
}

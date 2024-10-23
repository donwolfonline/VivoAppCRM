<?php

namespace App\Exports;

use App\Models\Export;
use App\Models\Lead;
use App\Models\LeadStage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class LeadExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // $data = ProjectTask::where('project_id' ,$this->id)->get();
        // $data = Lead::where('created_by', \Auth::user()->id)->get();
        // $data = Lead::where('assign_to', '=', \Auth::user()->creatorId())->get();
        $data = Lead::where('created_by', '=', \Auth::user()->creatorId())->get();
   
        foreach($data as $k => $leads)
        { 
             unset($leads->sources, $leads->items,$leads->notes,$leads->labels,$leads->order,$leads->created_at,$leads->is_active,$leads->created_by,$leads->is_converted,$leads->date,$leads->	updated_at);
            $data[$k]["id"]            = $leads->id;
            $data[$k]["name"]         = $leads->name;
            $data[$k]["email"]      = $leads->email;
            $data[$k]["subject"]      = $leads->subject;
            $data[$k]["phone_no"]      = $leads->phone_no;  
            $data[$k]["user_id"]    = !empty($leads->userEmp) ? $leads->userEmp->name : '';
            $data[$k]["pipeline_id"]   = !empty($leads->pipeline) ? $leads->pipeline->name : '';
            $data[$k]["stage_id"]       = !empty($leads->stage) ? $leads->stage->name : '';
           
        }  
 
        return $data;
    }

    public function headings(): array
    {
        return [
            "id",
            "name",
            "email",
            "subject",
            "phone_no",
            "user",
            "pipeline",
            "stage",
          
        ];
    }
}

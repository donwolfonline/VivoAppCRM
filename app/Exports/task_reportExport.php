<?php

namespace App\Exports;

use App\Models\Project;
use App\Models\project_report;
use App\Models\Milestone;
use App\Models\ProjectTask;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class task_reportExport implements FromCollection,WithHeadings
{


   
        public function collection()
        {

       
        // $data = ProjectTask::where('project_id' ,$this->id)->get();
        $data = ProjectTask::where('assign_to', \Auth::user()->id)->get();
       

        foreach($data as $k => $tasks)
        {
            unset($tasks->project_id, $tasks->order,$tasks->created_at,$tasks->updated_at);

            $user_name =   project_report::assign_user($tasks->assign_to);
            $milestone_name =   project_report::milestone($tasks->milestone_id);
            $status_name =   project_report::status($tasks->stage);
            $data[$k]["id"]            = $tasks->id;
            $data[$k]["title"]         = $tasks->title;
            $data[$k]["priority"]      = $tasks->priority;
            $data[$k]["description"]    = $tasks->description;
            $data[$k]["start_date"]     = $tasks->start_date;
            $data[$k]["due_date"]       = $tasks->due_date;
            $data[$k]["assign_to"]      = $user_name;
            $data[$k]["milestone_id"]   = $milestone_name;
            $data[$k]["status"]         = $status_name;

           
        }  

        return $data;
    }

    public function headings(): array
    {
        return [
            "ID",
            "Title",
            "Priority",
            'Description',
            "Start Date",
            "End Date",
            "Assign To",
            "Milestone",
            "Status",
        ];
    }
    
}
     
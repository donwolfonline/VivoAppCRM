<?php

namespace App\Exports;

use App\Models\Export;
use App\Models\User;
use App\Models\ProjectTask;
use App\Models\Timesheet;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class TimelogExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function collection()
    {
        $data = Timesheet::where('created_by', '=', \Auth::user()->creatorId())->get();
       

        foreach($data as $k => $timesheets)
        {


            unset($timesheets->project_id,$timesheets->task_id,$timesheets->client_view, $timesheets->created_by,$timesheets->created_at,$timesheets->updated_at);
            $data[$k]["id"]            = $timesheets->id;
            $data[$k]["employee"]      = $timesheets->employee;
            $data[$k]["start_date"]      = $timesheets->start_date;
            $data[$k]["start_time"]      = $timesheets->start_time;
            $data[$k]["start_date"]      = $timesheets->start_date;
            $data[$k]["end_date"]      = $timesheets->end_date;
            $data[$k]["end_time"]      = $timesheets->end_time;
            $data[$k]["notes"]      = $timesheets->notes;


        }

        return $data;
    }

    public function headings(): array
    {
        return [
            "id",
            "employee",
            "start_date",
            "start_time",
            "end_date",
            "end_time",
            "notes"
        ];
    }
}

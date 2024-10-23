<?php

namespace App\Exports;

use App\Models\Meeting;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MeetingExport implements FromCollection , WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = Meeting::where('created_by', '=', \Auth::user()->creatorId())->get();
        foreach($data as $k => $meeting)
        {
            if($meeting->department == 0 && $meeting->designation == 0)
            {
                $desigantion= 'All';
                $deapartment= 'All';
            }
            else
            {
                $desigantion  = Meeting::designation($meeting->designation);
                $deapartment  = Meeting::department($meeting->department);
            }
            $data[$k]["designation"]            = $desigantion;
            $data[$k]["department"]            = $deapartment;
            unset($meeting->id,$meeting->created_by,$meeting->created_at,$meeting->updated_at);

        }
        return $data;
    }

    public function headings(): array
    {
        return [
            "Department",
            "Designation",
            "Title",
            "Date",
            "Time",
            "Notes",
        ];
    }
}

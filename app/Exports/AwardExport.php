<?php

namespace App\Exports;

use App\Models\Award;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AwardExport implements FromCollection , WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = Award::where('created_by', '=', \Auth::user()->creatorId())->get();
        foreach($data as $k => $award)
        {
            $employees  = Award::employees($award->employee_id);
            $awardtype  = Award::awardtypes($award->award_type);
            unset($award->id,$award->created_by,$award->created_at,$award->updated_at);
            $data[$k]["employee_id"]            = $employees;
            $data[$k]["award_type"]             = $awardtype;
        }
        return $data;
    }

    public function headings(): array
    {
        return [
            "Employee ID",
            "Award Type",
            "Date",
            "Gift",
            "Description",
        ];
    }

}

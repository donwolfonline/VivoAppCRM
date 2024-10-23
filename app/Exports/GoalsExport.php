<?php

namespace App\Exports;

use App\Models\Goal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GoalsExport implements FromCollection ,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = Goal::where('created_by', '=', \Auth::user()->creatorId())->get();
        foreach($data as $k => $goals)
        {
            unset($goals->id,$goals->created_by,$goals->created_at,$goals->updated_at);
            $data[$k]["goal_type"]   = Goal::$goalType[$goals->goal_type];
        }
        return $data;
    }

    public function headings(): array
    {
        return [
            "Name",
            "Amount",
            "Goal Type",
            "From",
            "To",
            "Display",
        ];
    }



}

<?php

namespace App\Exports;
use App\Models\Export;
use App\Models\Leave;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeaveReportExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {

        $employees = User::where('type', 'employee')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $employees->prepend('Select Employee', '');

         if(\Auth::user()->type == 'company')
        {
            $leaves = Leave::where('created_by', '=', \Auth::user()->creatorId());
        }
        else
        {
            $leaves = Leave::where('employee_id', '=', \Auth::user()->id);
        }

        if(!empty($request->employee))
        {
            $leaves->where('employee_id', $request->employee);
        }

        if(!empty($request->start_date))
        {
            $leaves->where('start_date', '>=', $request->start_date);
        }

        if(!empty($request->end_date))
        {
            $leaves->where('end_date', '<=', $request->end_date);
        }

        $leaves = $leaves->get();

       // $data = Leave::where('created_by', '=', \Auth::user()->creatorId())->get();
       
       foreach($leaves as $k => $leave)
       {
         
           unset($leave->total_leave_days, $leave->remark,$leave->created_by,$leave->created_at,$leave->updated_at);
           $leaves[$k]["id"]               = $leave->id;
           $leaves[$k]["employee_id"]         = !empty($leave->user)?$leave->user->name:'';
           $leaves[$k]["leave_type"]       =  !empty($leave->leaveType)?$leave->leaveType->title:'';
           $leaves[$k]["applied_on"]       = $leave->applied_on;
           $leaves[$k]["start_date"]       = $leave->start_date;
           $leaves[$k]["end_date"]         = $leave->end_date;
           $leaves[$k]["leave_reason"]     = $leave->leave_reason;
           $leaves[$k]["status"]           = $leave->status;
           
           
        }

        return $leaves;
    }

    public function headings(): array
    {
        return [

            "id",
            "employee_id",
            "leave_type",
            "applied_on",
            "start_date",
            "end_date",
            "leave_reason",
            "status",
        ];
    }
}

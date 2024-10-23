<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeeExport implements FromCollection , WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = User::where('type','employee')->where('created_by', '=', \Auth::user()->creatorId())->get();
        foreach($data as $k => $customer)
        {
            $data[$k]["customer_id"] = \Auth::user()->employeeIdFormat($customer->id);
            unset($customer->id,$customer->password, $customer->lang,
            $customer->created_by, $customer->email_verified_at,
            $customer->mode, $customer->remember_token,
            $customer->plan,$customer->plan_expire_date,
            $customer->requested_plan,$customer->default_pipeline,
            $customer->dark_mode,$customer->active_status,
            $customer->lastlogin,$customer->avatar);

        }

        return $data;
    }

    public function headings(): array
    {
        return [
            "Name",
            "Email",
            "type",
            "Active Status",
            "delete_status",
            "created_at",
            "updated_at",
            "Messenger Color",
            "Employee Id",

        ];
    }

}


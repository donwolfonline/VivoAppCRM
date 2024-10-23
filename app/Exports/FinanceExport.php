<?php

namespace App\Exports;

use App\Models\Export;
use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FinanceExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = Invoice::where('created_by', '=', \Auth::user()->creatorId())->get();
       

        foreach($data as $k => $invoices)
        {

            unset($invoices->id,$invoices->send_date,$invoices->tax,$invoices->status,$invoices->created_by, $invoices->created_by,$invoices->created_at,$invoices->updated_at);
            $data[$k]["invoice_id"]            = $invoices->invoice_id;
            $data[$k]["issue_date"]      = $invoices->issue_date;
            $data[$k]["due_date"]      = $invoices->due_date;
            $data[$k]["client"]      = $invoices->client;
            $data[$k]["project"]      = $invoices->project;
            $data[$k]["type"]      = $invoices->type;
            $data[$k]["description"]      = $invoices->description;


        }

        return $data;
    }

    public function headings(): array
    {
        return [
        "invoice_id'",
        "issue_date",
        "due_date",
        "client",
        "project",
        "type",
        "description",
        ];
    }
}

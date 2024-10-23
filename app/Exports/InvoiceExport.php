<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InvoiceExport implements FromCollection , WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = Invoice::where('created_by', '=', \Auth::user()->creatorId())->get();
        $invoice = Invoice::where('type' , 'project')->get();

        foreach($data as $k => $invoice)
        {
            $client  = Invoice::client($invoice->client);
            if($invoice->project == 0 && $invoice->type=='Product')
            {
                $project='-';
                $tax='-';
            }
            else
            {

                $project = Invoice::project($invoice->project);
                $tax     = Invoice::tax($invoice->tax);
            }

            $data[$k]["invoice_id"] = \Auth::user()->InvoiceNumberFormat($invoice->invoice_id);
            unset($invoice->send_date,$invoice->status,$invoice->id,$invoice->created_by,$invoice->created_at,$invoice->updated_at);
            $data[$k]["client"]            = $client;
            $data[$k]["project"]           = $project;
            $data[$k]["tax"]               = $tax;
        }
        return $data;
    }

    public function headings(): array
    {
        return [
            "Invoice ID",
            "Issue Date",
            "Due Date",
            "Client",
            "Project",
            "Tax",
            "Type",
            "Description",
        ];
    }
}

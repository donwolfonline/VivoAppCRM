<?php

namespace App\Exports;

use App\Models\CreditNote;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CreditNotesExport implements FromCollection , WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = CreditNote::where('created_by', '=', \Auth::user()->creatorId())->get();
        foreach($data as $k => $creditnote)
        {
            $clients  = CreditNote::clients($creditnote->client);

            $data[$k]["invoice"] = \Auth::user()->InvoiceNumberFormat($creditnote->invoice);
            unset($creditnote->id,$creditnote->created_by,$creditnote->created_at,$creditnote->updated_at);
            $data[$k]["client"]            = $clients;
        
        }
        return $data;
    }

    public function headings(): array
    {
        return [
            "Invoice ID",
            "Client",
            "Amount",
            "Date",
            "Description",
        ];
    }
}

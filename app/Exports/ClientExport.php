<?php

namespace App\Exports;

use App\Models\Client;
use App\Models\Export;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class ClientExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = Client::where('created_by', '=', \Auth::user()->creatorId())->get();
       
        foreach($data as $k => $clients)
        {

            unset($clients->website,$clients->tax_number,$clients->notes,$clients->address_1, $clients->address_2,$clients->city,$clients->zip_code,$clients->state,$clients->country,
            $clients->created_at,$clients->updated_at,$clients->created_by);
            $data[$k]["id"]            = $clients->id;
            $data[$k]["user_id"]            = $clients->user_id;
            $data[$k]["client_id"]            = $clients->client_id;
            $data[$k]["mobile"]      = $clients->mobile;
            $data[$k]["company_name"]      = $clients->company_name;

        }

        return $data;
    }

    public function headings(): array
    {
        return [
            "id",
            "user_id",
            "client_id",
            "mobile",

        ];
    }
}

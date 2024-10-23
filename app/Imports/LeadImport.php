<?php

namespace App\Imports;

use App\Models\Lead;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;

class LeadImport implements ToModel
{
    use Importable;

    public function model(array $row)
    {
        //
    }
}



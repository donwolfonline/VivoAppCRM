<?php

namespace App\Imports;

use App\Models\Deal;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;

class DealImport implements ToModel
{
    use Importable;

    public function model(array $row)
    {
        //
    }
}



<?php

namespace App\Imports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;

class ClientImport implements ToModel
{
   use Importable;

    public function model(array $row)
    {
        //
    }
}

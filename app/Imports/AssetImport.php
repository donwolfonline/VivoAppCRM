<?php

namespace App\Imports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;

class AssetImport implements ToModel
{
    use Importable;

    public function model(array $row)
    {
       //
    }
}

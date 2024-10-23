<?php

namespace App\Imports;

use App\Models\Holiday;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;

class HolidayImport implements ToModel
{
    use Importable;

    public function model(array $row)
    {
        // 
    }
}

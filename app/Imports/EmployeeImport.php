<?php

namespace App\Imports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;

class EmployeeImport implements ToModel
{
    use Importable;

    public function model(array $row)
    {
        //
    }
}



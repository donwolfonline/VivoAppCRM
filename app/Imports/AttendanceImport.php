<?php

namespace App\Imports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;

class AttendanceImport implements ToModel
{
    use Importable;

    public function model(array $row)
    {
       //
    }
}

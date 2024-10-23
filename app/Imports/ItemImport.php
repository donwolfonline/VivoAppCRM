<?php

namespace App\Imports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;

class ItemImport implements ToModel
{
    use Importable; 

    public function model(array $row)
    {
       //
    }
}

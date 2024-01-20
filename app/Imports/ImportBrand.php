<?php

namespace App\Imports;
use App\Models\BranddMaster;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportBrand implements ToModel
{
    /**
    * @param Collection $collection
    */
     public function model(array $row)
    {
       
        return new BranddMaster([
           
            'brand' => isset($row[0]) ? trim($row[0]) : '',
            'disc' => isset($row[1]) ? trim($row[1]) : '',
        ]);
    }
}

<?php

namespace App\Imports;
use App\Models\BarcodeMaster;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportBarcode implements ToModel
{
    /**
    * @param Collection $collection
    */
     public function model(array $row)
    {
       
        return new BarcodeMaster([
           
            'barcode' => isset($row[0]) ? trim($row[0]) : '',
            'product_name' => isset($row[1]) ? trim($row[1]) : '',
            'fixdis' => isset($row[2]) ? trim($row[2]) : '',
        ]);
    }
}

<?php

namespace App\Imports;
use App\Models\ExcelProdds;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportExcel implements ToModel
{
    /**
    * @param Collection $collection
    */
     public function model(array $row)
    {
       
        return new ExcelProdds([
            'product_id' => isset($row[0]) ? trim($row[0]) : '',
            'product_name' => isset($row[1]) ? trim($row[1]) : '',
            'barcode' => isset($row[2]) ? trim($row[2]) : '',
            'hsncode' => isset($row[3]) ? trim($row[3]) : '',
            'cgst' => isset($row[4]) ? trim($row[4]) : '',
            'sgst' => isset($row[5]) ? trim($row[5]) : '',
            'igst' => isset($row[6]) ? trim($row[6]) : '',
            'prate' => isset($row[7]) ? trim($row[7]) : '',
            'mrp' => isset($row[8]) ? trim($row[8]) : '',
            'stock' => isset($row[9]) ? trim($row[9]) : '',
            'category' => isset($row[10]) ? trim($row[10]) : ''
        ]);
    }
}

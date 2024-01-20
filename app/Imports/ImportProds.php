<?php

namespace App\Imports;

use App\Models\ExcelProds;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportProds implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if (!isset($row[0])) {
            return;
        }
        return new ExcelProds([
            'product_name' => isset($row[0]) ? trim($row[0]) : '',
            'category' => isset($row[3]) ? trim($row[3]) : '',
            'sub_category' => isset($row[4]) ? trim($row[4]) : '',
            'sub_sub_category' => isset($row[5]) ? trim($row[5]) : '',
            'base_quantity' => isset($row[1]) ? ((trim($row[1]) < 0) ? 0 :  trim($row[1])) : '',
            'brand_name' => isset($row[6]) ? trim($row[6]) : '',
            'barcode' => isset($row[2]) ? trim($row[2]) : '',
            'purchase' => isset($row[7]) ? trim($row[7]) : '',
            'tax' => isset($row[13]) ? trim($row[13]) : '',
            'selling_price' => isset($row[10]) ? trim($row[10]) : '',
            'mrp' => isset($row[8]) ? trim($row[8]) : '',
            'discount' => isset($row[9]) ? trim($row[9]) : ''
        ]);
    }
}

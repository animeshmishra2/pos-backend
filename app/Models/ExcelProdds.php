<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExcelProdds extends Model
{
    use HasFactory;
    protected $table = 'excel_prodds';

    /**
     * The database primary key value.
     *
     * @var string
     */
  

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'product_id',
        'product_name',
        'barcode',
        'hsncode',
        'sgst',
        'cgst',
        'igst',
        'prate',
        'mrp',
        'stock',
        'category'
    ];
}

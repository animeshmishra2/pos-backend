<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExcelProds extends Model
{
    use HasFactory;
    protected $table = 'excel_prods';

    /**
     * The database primary key value.
     *
     * @var string
     */
    protected $primaryKey = 'idexcel_prods';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_name',
        'category',
        'sub_category',
        'sub_sub_category',
        'sub_sub_sub_category',
        'base_quantity',
        'brand_name',
        'barcode',
        'purchase',
        'tax',
        'mrp',
        'selling_price',
        'discount',
        'updated_at',
        'created_at',
        'status'
    ];
}

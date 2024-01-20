<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissingProductMaster extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'missing_product_master';

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
    protected $fillable = ['idmissing_product_master', 'barcode', 'name', 'mrp', 'qty', 'selling_price','purchase_price', 'status', 'sgst', 'cgst', 'igst', 'store_id', 'counter_id'];

    
}

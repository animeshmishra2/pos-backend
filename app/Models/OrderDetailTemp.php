<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetailTemp extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'order_detail_temp';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idorder_detail_temp';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idcustomer_order_temp', 'idproduct_master', 'idinventory', 'quantity', 'total_price', 'total_cgst', 'total_sgst', 'created_by', 'updated_by', 'status', 'unit_mrp','instant_discount','product_discount','copartner_discount','land_discount','unit_selling_price', 'discount'];

    
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'product_batch';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idproduct_batch';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idstore_warehouse', 'idproduct_master','name', 'purchase_price', 'selling_price', 'mrp','product','copartner','land' ,'discount', 'quantity', 'expiry', 'created_by', 'updated_by', 'status'];

    
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductMaster extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'product_master';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idproduct_master';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idcategory', 'idsub_category', 'idsub_sub_category', 'idbrand', 'purchase_price', 'name', 'description', 'barcode', 'hsn', 'cgst', 'sgst','cess' ,'created_by', 'updated_by', 'status'];

    
}

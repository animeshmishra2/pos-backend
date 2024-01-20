<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorPurchasesDetail extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'vendor_purchases_detail';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idvendor_purchases_detail';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idvendor_purchases','selling_price', 'idproduct_master', 'mrp','product','copartner','land','hsn', 'quantity', 'unit_purchase_price', 'free_quantity', 'expiry', 'created_by', 'updated_by', 'status'];

    
}

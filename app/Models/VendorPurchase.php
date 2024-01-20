<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorPurchase extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'vendor_purchases';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idvendor_purchases';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idvendor', 'bill_number', 'idstore_warehouse', 'total', 'cgst', 'sgst','igst','pay_mode', 'paid','balance','bill_date','pending_value','bill_remark','items', 'quantity', 'created_by', 'updated_by', 'status'];

    
}

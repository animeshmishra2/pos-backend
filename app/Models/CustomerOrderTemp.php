<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerOrderTemp extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'customer_order_temp';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idcustomer_order_temp';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idstore_warehouse', 'idcustomer', 'cart_id', 'is_online', 'is_pos', 'is_paid_online', 'is_paid', 'is_delivery', 'total_quantity', 'total_price', 'total_cgst', 'total_sgst', 'total_discount', 'discount_type', 'promocode', 'created_by', 'updated_by', 'status'];

    
}

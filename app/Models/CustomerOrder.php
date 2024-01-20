<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerOrder extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'customer_order';

    /**
     * The database primary key value.
     *
     * @var string
     */
     

    protected $primaryKey = 'idcustomer_order';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idstore_warehouse','idmembership_plan', 'iddelivery_slots', 'idcounter', 'idcustomer', 'is_online', 'is_pos', 'is_paid_online', 'is_paid', 'pay_mode_ref', 'pay_mode', 'is_delivery','idcustomer_address', 'total_quantity', 'total_price', 'total_cgst', 'total_sgst', 'total_discount','instant_discount','product_discount', 'copartner_discount','land_discount','extraDisc','discount_type', 'discount_detail', 'created_by', 'updated_by', 'status', 'redeemed_amt', 'total_before_redeem'];
}

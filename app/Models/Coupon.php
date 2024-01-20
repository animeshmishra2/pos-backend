<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'coupons';

    /**
     * The database primary key value.
     *
     * @var string
     */
     

    protected $primaryKey = 'idcoupon';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idstore_warehouse','name', 'minordervalue','discount_percentage', 'discount','uptomax_amount', 'usable_days', 'isgeneral', 'reuse_limit', 'status', 'active_from', 'active_till','created_by', 'updated_by'];
}

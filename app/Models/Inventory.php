<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'inventory';

    /**
     * The database primary key value.
     *
     * @var string
     */
    protected $primaryKey = 'idinventory';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idstore_warehouse', 'idproduct_master', 'selling_price', 'purchase_price', 'purchase_price', 'mrp','product','copartner','land','discount', 'quantity', 'only_online', 'only_offline', 'created_by', 'updated_by', 'status', 'instant_discount_percent', 'listing_type'];
}

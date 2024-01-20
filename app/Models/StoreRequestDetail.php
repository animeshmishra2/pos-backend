<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreRequestDetail extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'store_request_detail';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idstore_request_detail';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idproduct_master','idstore_request', 'idstore_warehouse_to', 'idstore_warehouse_from', 'idstore_request_detail', 'quantity', 'quantity_sent', 'created_by', 'updated_by', 'status'];

    
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreRequest extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'store_request';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idstore_request';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['dispatch_date','request_type','old_idstore_request', 'dispatch_detail', 'dispatched_by', 'idstore_request', 'idstore_warehouse_to', 'idstore_warehouse_from', 'status', 'created_by', 'updated_by', 'status'];

    
}

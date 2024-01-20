<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'customer_address';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idcustomer_address';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idcustomer', 'name', 'address', 'pincode', 'landmark', 'is_default', 'phone', 'created_by', 'updated_by', 'status', 'lat', 'long', 'tag'];

    
}

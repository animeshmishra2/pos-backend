<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'customer';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idcustomer';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idstore_warehouse', 'name', 'phone', 'email', 'idmembership', 'wallet_balance', 'created_by', 'updated_by', 'status'];

    
}

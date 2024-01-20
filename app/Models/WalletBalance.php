<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletBalance extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'wallet_balance';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idwallet_balance';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idcustomer', 'idmembership_plan', 'total_incurred', 'current_amount', 'redeemed', 'created_by', 'updated_by', 'status'];

    
}

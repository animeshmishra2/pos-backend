<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'wallet_transaction';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idwallet_transaction';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idwallet_transaction', 'idcustomer', 'type', 'idmembership_plan', 'ref_id', 'amount', 'transaction_type', 'remark', 'created_by', 'updated_by', 'status'];

    
}

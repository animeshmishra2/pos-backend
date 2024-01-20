<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'payment';

    /**
     * The database primary key value.
     *
     * @var string
     */
    protected $primaryKey = 'idpayment';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'idcustomer_order',
        'txn_id',
        'payment_complete',
        'payment_gateway_res',
        'gateway_status',
        'log',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'status'
    ];
}

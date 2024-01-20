<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CounterTransaction extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'idcounter_transaction';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idcounter_transaction';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idcounters_login', 'amount', 'is_inbound', 'details', 'created_by', 'updated_by', 'status'];

    
}

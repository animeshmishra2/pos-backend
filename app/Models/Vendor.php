<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'vendor';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idvendor';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'idstore_warehouse', 'address', 'phone', 'email','bank_name','benificiary_name','acc_no','ifsc', 'gst','state','city','payment_type','credit_day','payment_details', 'created_by', 'updated_by', 'status'];

    
}

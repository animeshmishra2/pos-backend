<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Counter extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'counter';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idcounter';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idstore_warehouse', 'name', 'live_status', 'created_by', 'updated_by', 'status'];

    
}

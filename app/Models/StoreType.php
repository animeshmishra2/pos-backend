<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreType extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'store_type';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idstore_type';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idstore_type', 'name', 'cost', 'status'];

    
}

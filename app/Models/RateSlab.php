<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateSlab extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rate_slab';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idrate_slab';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idrate_slab', 'idpackage', 'from_amount', 'till_amount', 'additional_amount', 'created_by', 'updated_by', 'status'];

    
}

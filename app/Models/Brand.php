<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'brands';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idbrand';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idbrand', 'name', 'logo', 'created_by', 'updated_by', 'status'];

    
}

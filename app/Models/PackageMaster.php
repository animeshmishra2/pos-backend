<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageMaster extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'package_master';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idpackage_master';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idpackage_master', 'name', 'triggered_on', 'created_by', 'updated_by', 'status'];

    
}

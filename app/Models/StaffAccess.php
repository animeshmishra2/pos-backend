<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAccess extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'staff_access';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idstaff_access';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idstaff', 'idstore_warehouse', 'idaccess_level', 'created_by', 'updated_by', 'status'];

    
}

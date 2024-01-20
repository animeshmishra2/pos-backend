<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'package';

    /**
     * The database primary key value.
     *
     * @var string
     */
    protected $primaryKey = 'idpackage';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idpackage', 'name', 'idpackage_master', 'idstore_warehouse', 'applicable_on', 'frequency', 'base_trigger_amount', 'additional_tag_amount', 'bypass_make_gen', 'valid_from', 'valid_till', 'created_by', 'updated_by', 'status'];
}

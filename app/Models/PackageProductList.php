<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageProductList extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'package_prod_list';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idpackage_prod_list';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idpackage_prod_list', 'idpackage', 'idproduct_master', 'quantity', 'is_triggerer_tag_along', 'created_by', 'updated_by', 'status'];

    
}

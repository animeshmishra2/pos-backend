<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubSubCategory extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sub_sub_category';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idsub_sub_category';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idsub_sub_category', 'idsub_category', 'idcategory', 'name', 'image', 'description', 'category_for', 'created_by', 'updated_by', 'status'];

    
}

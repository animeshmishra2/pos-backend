<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sub_category';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idsub_category';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idsub_category', 'idcategory', 'name', 'image', 'cat_icon', 'description', 'category_for', 'created_by', 'updated_by', 'status'];

    
}

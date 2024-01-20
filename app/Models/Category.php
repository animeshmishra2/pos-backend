<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'category';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idcategory';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['idcategory', 'name', 'image', 'cat_icon', 'description', 'category_for','has_return_rule','return_type','return_duration','created_by', 'updated_by', 'status'];

    
}

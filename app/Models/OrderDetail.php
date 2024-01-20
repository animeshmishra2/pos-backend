<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'order_detail';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idorder_detail';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
     
    protected $fillable = ['idcustomer_order','part_of_pkg', 'idpackage','pkg_amount','remark', 'idproduct_master', 'idinventory', 'quantity', 'total_price', 'total_cgst', 'total_sgst', 'created_by', 'updated_by', 'status'];

    
}

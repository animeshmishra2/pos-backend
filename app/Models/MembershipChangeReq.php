<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipChangeReq extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'membership_change_req';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idmembership_change_req';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'from_membership', 'to_membership', 'remark', 'created_by', 'updated_by', 'status'];
}

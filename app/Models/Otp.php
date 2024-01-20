<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'otp';

    /**
    * The database primary key value.
    *
    * @var string
    */
    protected $primaryKey = 'idotp';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'otp', 'attempt', 'status'];

    
}

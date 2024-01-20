<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranddMaster extends Model
{
    use HasFactory;
    protected $table = 'brandd_masters';

    /**
     * The database primary key value.
     *
     * @var string
     */
  

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'brand',
        'disc',
    ];
}

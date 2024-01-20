<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliverySlots extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'delivery_slots';

    /**
     * The database primary key value.
     *
     * @var string
     */
    protected $primaryKey = 'iddelivery_slots';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        "idstore_warehouse",
        "date",
        "is_servicable",
        "slot_time_start",
        "slot_time_end",
        "max_orders",
        "created_by",
        "updated_by",
        "status",
        "available_slots"
    ];
}

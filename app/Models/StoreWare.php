<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreWare extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'store_warehouse';

    /**
     * The database primary key value.
     *
     * @var string
     */
    protected $primaryKey = 'idstore_warehouse';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        "name",
        "gst",
        "bill_header",
        "address",
        "city",
        "pincode",
        "is_store",
        "idstore_type",
        "contact",
        "is_copartner",
        "lat",
        "long",
        "support_delivery",
        "advance_delivery_day",
        "slot_duration",
        "slot_time_start",
        "slot_time_end",
        "max_orders",
        "created_at",
        "updated_at",
        "created_by",
        "updated_by",
        "status",
        "advance_delivery_day",
        "service_distance"
    ];
}

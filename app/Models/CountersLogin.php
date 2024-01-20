<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountersLogin extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'counters_login';

    /**
     * The database primary key value.
     *
     * @var string
     */
    protected $primaryKey = 'idcounters_login';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'idcounter', 'idstaff', 'open_balance', 'close_balance', 'open_cash_detail', 'close_cash_detail', 'online_payments', 'created_by', 'updated_by', 'status', 'od_1',
        'od_2',
        'od_5',
        'od_10',
        'od_20',
        'od_50',
        'od_100',
        'od_200',
        'od_500',
        'od_2000',
        'cd_1',
        'cd_2',
        'cd_5',
        'cd_10',
        'cd_20',
        'cd_50',
        'cd_100',
        'cd_200',
        'cd_500',
        'cd_2000'
    ];
}

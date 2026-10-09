<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

class NewRentOrderLog extends Model
{
    protected $table = 'trade_new_rent_order_log';
    protected $primaryKey = 'log_id';
    public $timestamps = false;
    protected $guarded = [];
}

<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

class NewOrderLog extends Model
{
    protected $table = 'trade_new_order_log';
    protected $primaryKey = 'log_id';
    public $timestamps = false;
    protected $guarded = [];
}

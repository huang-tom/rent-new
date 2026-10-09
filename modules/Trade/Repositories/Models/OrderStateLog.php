<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class OrderStateLog.
 *
 * @package Modules\Trade\Repositories\Models
 */
class OrderStateLog extends Model
{

    protected $table      = 'trade_order_state_log';
    protected $primaryKey = 'order_state_log_id';
    public $timestamps    = false;

    protected $guarded = [];
}

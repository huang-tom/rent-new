<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderReturnReason.
 *
 * @package Modules\Trade\Repositories\Models
 */
class OrderReturnReason extends Model
{

    protected $table = 'trade_order_return_reason';
    protected $primaryKey = 'return_reason_id';
    public $timestamps = false;

    protected $guarded = [];
}

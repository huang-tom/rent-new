<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

class NewOrderRefund extends Model
{
    protected $table = 'trade_new_order_refund';
    protected $primaryKey = 'refund_id';
    public $timestamps = false;
    protected $guarded = [];
}

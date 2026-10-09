<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

class NewRentOrderRefund extends Model
{
    protected $table = 'trade_new_rent_order_refund';
    protected $primaryKey = 'refund_id';
    public $timestamps = false;
    protected $guarded = [];
}

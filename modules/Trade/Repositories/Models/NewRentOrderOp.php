<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

class NewRentOrderOp extends Model
{
    protected $table = 'trade_new_rent_order_op';
    protected $primaryKey = 'op_id';
    public $timestamps = false;
    protected $guarded = [];
}

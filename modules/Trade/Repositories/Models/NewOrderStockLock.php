<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

class NewOrderStockLock extends Model
{
    protected $table = 'trade_new_order_stock_lock';
    protected $primaryKey = 'lock_id';
    public $timestamps = false;
    protected $guarded = [];
}

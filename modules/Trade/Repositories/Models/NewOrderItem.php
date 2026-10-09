<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

class NewOrderItem extends Model
{
    protected $table = 'trade_new_order_item';
    protected $primaryKey = 'item_id';
    public $timestamps = false;
    protected $guarded = [];
}

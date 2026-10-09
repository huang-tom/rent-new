<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

class NewRentOrderItem extends Model
{
    protected $table = 'trade_new_rent_order_item';
    protected $primaryKey = 'item_id';
    public $timestamps = false;
    protected $guarded = [];
}

<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

class NewRentOrder extends Model
{
    protected $table = 'trade_new_rent_order';
    protected $primaryKey = 'order_id';
    public $timestamps = false;
    protected $guarded = [];
}

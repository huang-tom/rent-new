<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

class NewOrderAddress extends Model
{
    protected $table = 'trade_new_order_address';
    protected $primaryKey = 'address_id';
    public $timestamps = false;
    protected $guarded = [];
}

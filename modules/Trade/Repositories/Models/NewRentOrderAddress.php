<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

class NewRentOrderAddress extends Model
{
    protected $table = 'trade_new_rent_order_address';
    protected $primaryKey = 'address_id';
    public $timestamps = false;
    protected $guarded = [];
}

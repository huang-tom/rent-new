<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

class NewRentOrderRepair extends Model
{
    protected $table = 'trade_new_rent_order_repair';
    protected $primaryKey = 'repair_id';
    public $timestamps = false;
    protected $guarded = [];
}

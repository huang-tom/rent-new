<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

class NewOrder extends Model
{
    protected $table = 'trade_new_order';
    protected $primaryKey = 'order_id';
    public $timestamps = false;
    protected $guarded = [];
}

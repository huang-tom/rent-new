<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class OrderReturnItem.
 *
 * @package Modules\Trade\Repositories\Models
 */
class OrderReturnItem extends Model
{

    protected $table      = 'trade_order_return_item';
    protected $primaryKey = 'order_return_item_id';
    public $timestamps    = false;

    protected $guarded = [];
}

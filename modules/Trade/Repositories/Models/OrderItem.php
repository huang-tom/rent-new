<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class OrderItem.
 *
 * @package Modules\Trade\Repositories\Models
 */
class OrderItem extends Model
{

    protected $table      = 'trade_order_item';
    protected $primaryKey = 'order_item_id';
    public $timestamps    = false;

    protected $guarded = [];
}

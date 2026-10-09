<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class OrderDeliveryAddress.
 *
 * @package Modules\Trade\Repositories\Models
 */
class OrderDeliveryAddress extends Model
{

    protected $table      = 'trade_order_delivery_address';
    protected $primaryKey = 'order_id';
    protected $keyType    = 'string';
    public $timestamps    = false;

    protected $guarded = [];
}

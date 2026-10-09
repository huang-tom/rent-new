<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class OrderInfo.
 *
 * @package Modules\Trade\Repositories\Models
 */
class OrderInfo extends Model
{

    protected $table      = 'trade_order_info';
    protected $primaryKey = 'order_id';
    protected $keyType    = 'string';
    public $timestamps    = false;

    protected $guarded = [];
}

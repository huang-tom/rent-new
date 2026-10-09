<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class OrderLogistics.
 *
 * @package Modules\Trade\Repositories\Models
 */
class OrderLogistics extends Model
{

    protected $table      = 'trade_order_logistics';
    protected $primaryKey = 'order_logistics_id';
    public $timestamps    = false;

    protected $guarded = [];
}

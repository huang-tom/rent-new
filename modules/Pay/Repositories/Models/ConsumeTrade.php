<?php

namespace Modules\Pay\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ConsumeTrade.
 *
 * @package Modules\Pay\Repositories\Models
 */
class ConsumeTrade extends Model
{

    protected $table      = 'pay_consume_trade';
    protected $primaryKey = 'consume_trade_id';
    public $timestamps    = false;

    protected $guarded = [];
}

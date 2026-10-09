<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class OrderData.
 *
 * @package Modules\Trade\Repositories\Models
 */
class OrderData extends Model
{

    protected $table      = 'trade_order_data';
    protected $primaryKey = 'order_id';
    protected $keyType    = 'string';
    public $timestamps    = false;

    protected $guarded = [];
}

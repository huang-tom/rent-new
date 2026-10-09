<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class OrderBase.
 *
 * @package Modules\Trade\Repositories\Models
 */
class OrderBase extends Model
{

    protected $table      = 'trade_order_base';
    protected $primaryKey = 'order_id';
    protected $keyType    = 'string';
    public $timestamps    = false;

    protected $guarded = [];
}

<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class OrderReturn.
 *
 * @package Modules\Trade\Repositories\Models
 */
class OrderReturn extends Model
{

    protected $table      = 'trade_order_return';
    protected $primaryKey = 'return_id';
    protected $keyType    = 'string';
    public $timestamps    = false;

    protected $guarded = [];
}

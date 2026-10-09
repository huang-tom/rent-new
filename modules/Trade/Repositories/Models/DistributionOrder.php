<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class DistributionOrder.
 *
 * @package Modules\Trade\Repositories\Models
 */
class DistributionOrder extends Model
{

    protected $table      = 'trade_distribution_order';
    protected $primaryKey = 'uo_id';
    protected $keyType    = 'string';
    public $timestamps    = false;

    protected $guarded = [];
}

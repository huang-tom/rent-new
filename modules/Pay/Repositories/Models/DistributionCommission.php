<?php

namespace Modules\Pay\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DistributionCommission.
 *
 * @package Modules\Pay\Repositories\Models
 */
class DistributionCommission extends Model
{

    protected $table = 'pay_distribution_commission';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    protected $guarded = [];
}

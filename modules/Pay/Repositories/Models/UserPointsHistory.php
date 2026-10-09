<?php

namespace Modules\Pay\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class UserPointsHistory.
 *
 * @package Modules\Pay\Repositories\Models
 */
class UserPointsHistory extends Model
{

    protected $table      = 'pay_user_points_history';
    protected $primaryKey = 'points_log_id';
    public $timestamps    = false;

    protected $guarded = [];
}

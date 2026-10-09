<?php

namespace Modules\Marketing\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ActivityGroupBookingHistory.
 *
 * @package Modules\Marketing\Repositories\Models
 */
class ActivityGroupBookingHistory extends Model
{

    protected $table = 'marketing_activity_groupbooking_history';
    protected $primaryKey = 'gbh_id';
    public $timestamps = false;

    protected $guarded = [];
}

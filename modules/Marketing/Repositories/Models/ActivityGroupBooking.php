<?php

namespace Modules\Marketing\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ActivityGroupBookingHistory.
 *
 * @package Modules\Marketing\Repositories\Models
 */
class ActivityGroupBooking extends Model
{

    protected $table = 'marketing_activity_groupbooking';
    protected $primaryKey = 'gb_id';
    public $timestamps = false;

    protected $guarded = [];
}

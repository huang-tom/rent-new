<?php

namespace Modules\Marketing\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ActivityItem.
 *
 * @package Modules\Marketing\Repositories\Models
 */
class ActivityItem extends Model
{

    protected $table      = 'marketing_activity_item';
    protected $primaryKey = 'activity_item_id';
    public $timestamps    = false;

    protected $guarded = [];
}

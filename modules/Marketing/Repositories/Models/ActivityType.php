<?php

namespace Modules\Marketing\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ActivityType.
 *
 * @package Modules\Marketing\Repositories\Models
 */
class ActivityType extends Model
{

    protected $table = 'marketing_activity_type';
    protected $primaryKey = 'activity_type_id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'activity_type_enable' => 'boolean'
    ];
}

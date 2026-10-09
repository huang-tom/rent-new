<?php

namespace Modules\Marketing\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ActivityBase.
 *
 * @package Modules\Marketing\Repositories\Models
 */
class ActivityBase extends Model
{

    protected $table      = 'marketing_activity_base';
    protected $primaryKey = 'activity_id';
    public $timestamps    = false;

    protected $guarded = [];

    protected $casts = [
    	'activity_rule'=>'array'
    ];
}

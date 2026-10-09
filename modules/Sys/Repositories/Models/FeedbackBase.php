<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class FeedbackBase.
 *
 * @package Modules\Sys\Repositories\Models
 */
class FeedbackBase extends Model
{

    protected $table      = 'sys_feedback_base';
    protected $primaryKey = 'feedback_id';
    public $timestamps    = false;

    protected $guarded = [];
}

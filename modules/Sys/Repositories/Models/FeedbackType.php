<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class FeedbackType.
 *
 * @package Modules\Sys\Repositories\Models
 */
class FeedbackType extends Model
{

    protected $table = 'sys_feedback_type';
    protected $primaryKey = 'feedback_type_id';
    public $timestamps = false;

    protected $guarded = [];
    protected $casts = [
        'feedback_type_enable' => 'boolean'
    ];
}

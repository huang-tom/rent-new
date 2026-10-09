<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class FeedbackCategory.
 *
 * @package Modules\Sys\Repositories\Models
 */
class FeedbackCategory extends Model
{

    protected $table = 'sys_feedback_category';
    protected $primaryKey = 'feedback_category_id';
    public $timestamps = false;

    protected $guarded = [];
    protected $casts = [
        'feedback_category_enable' => 'boolean'
    ];
}

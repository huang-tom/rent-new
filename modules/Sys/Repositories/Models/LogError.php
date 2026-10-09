<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class LogError.
 *
 * @package Modules\Sys\Repositories\Models
 */
class LogError extends Model
{

    protected $table = 'sys_log_error';
    protected $primaryKey = 'log_error_id';
    public $timestamps = false;

    protected $guarded = [];
}

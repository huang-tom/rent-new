<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class LogAction.
 *
 * @package Modules\Sys\Repositories\Models
 */
class LogAction extends Model
{

    protected $table = 'sys_log_action';
    protected $primaryKey = 'log_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_account',
        'user_name',
        'log_name',
        'action_id',
        'action_type_id',
        'log_url',
        'log_method',
        'log_param',
        'log_ip',
        'log_date',
        'log_time',
    ];

    protected $dates = ['log_date'];
    protected $casts = [
        'log_param' => 'array'
    ];

}

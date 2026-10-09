<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CrontabBase.
 *
 * @package Modules\Sys\Repositories\Models
 */
class CrontabBase extends Model
{

    protected $table = 'sys_crontab_base';
    protected $primaryKey = 'crontab_id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'crontab_enable' => 'boolean',
        'crontab_buildin' => 'boolean'
    ];
}

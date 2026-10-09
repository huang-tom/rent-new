<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ConfigBase.
 *
 * @package Modules\Sys\Repositories\Models
 */
class ConfigBase extends Model
{

    protected $table = 'sys_config_base';
    protected $primaryKey = 'config_key';
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = [];
    protected $casts = [
        'config_enable' => 'boolean'
    ];
}

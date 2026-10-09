<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ConfigType.
 *
 * @package Modules\Sys\Repositories\Models
 */
class ConfigType extends Model
{

    protected $table      = 'sys_config_type';
    protected $primaryKey = 'config_type_id';
    public $timestamps    = false;

    protected $guarded = [];
}

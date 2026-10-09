<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class Config.
 *
 * @package Modules\Sys\Repositories\Models
 */
class Config extends Model
{

    protected $table      = 'sys_config_base';
    protected $primaryKey = 'config_key';
    protected $keyType    = 'string';
    public $timestamps    = false;

    protected $guarded = [];
}

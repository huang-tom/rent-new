<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DictBase.
 *
 * @package Modules\Sys\Repositories\Models
 */
class DictBase extends Model
{

    protected $table = 'sys_dict_base';
    protected $primaryKey = 'dict_id';
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = [];
}

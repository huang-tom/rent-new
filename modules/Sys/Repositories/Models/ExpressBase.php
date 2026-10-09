<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ExpressBase.
 *
 * @package Modules\Sys\Services\Models
 */
class ExpressBase extends Model
{

    protected $table      = 'sys_express_base';
    protected $primaryKey = 'express_id';
    public $timestamps    = false;

    protected $guarded = [];

    protected $casts = [
        'express_enable' => 'boolean'
    ];
}

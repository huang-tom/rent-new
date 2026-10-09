<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class DistrictBase.
 *
 * @package Modules\Sys\Repositories\Models
 */
class DistrictBase extends Model
{

    protected $table      = 'sys_district_base';
    protected $primaryKey = 'district_id';
    public $timestamps    = false;

    protected $guarded = [];
}

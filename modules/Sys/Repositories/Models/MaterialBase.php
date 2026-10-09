<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MaterialBase.
 *
 * @package Modules\Sys\Repositories\Models
 */
class MaterialBase extends Model
{

    protected $table      = 'sys_material_base';
    protected $primaryKey = 'material_id';
    public $timestamps    = false;

    protected $guarded = ['material_id'];
}

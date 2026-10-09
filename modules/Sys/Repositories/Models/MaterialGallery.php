<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MaterialGallery.
 *
 * @package Modules\System\Repositories\Models
 */
class MaterialGallery extends Model
{

    protected $table      = 'sys_material_gallery';
    protected $primaryKey = 'gallery_id';
    public $timestamps    = false;

    protected $guarded = ['gallery_id'];
}

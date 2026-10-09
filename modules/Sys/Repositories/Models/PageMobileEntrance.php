<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PageMobileEntrance.
 *
 * @package Modules\Sys\Repositories\Models
 */
class PageMobileEntrance extends Model
{

    protected $table = 'sys_page_mobile_entrance';
    protected $primaryKey = 'entrance_id';
    public $timestamps = false;

    protected $guarded = [];
    protected $casts = [
        'entrance_enable' => 'boolean'
    ];
}

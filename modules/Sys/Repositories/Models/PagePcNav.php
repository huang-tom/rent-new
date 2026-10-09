<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class PagePcNav.
 *
 * @package Modules\Sys\Repositories\Models
 */
class PagePcNav extends Model
{

    protected $table      = 'sys_page_pc_nav';
    protected $primaryKey = 'nav_id';
    public $timestamps    = false;

    protected $guarded = [];
    protected $casts = [
        'nav_enable' => 'boolean'
    ];
}

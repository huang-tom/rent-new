<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class PageCategoryNav.
 *
 * @package Modules\Sys\Repositories\Models
 */
class PageCategoryNav extends Model
{

    protected $table      = 'sys_page_category_nav';
    protected $primaryKey = 'category_nav_id';
    public $timestamps    = false;

    protected $guarded = [];
    protected $casts = [
        'category_nav_enable' => 'boolean'
    ];
}

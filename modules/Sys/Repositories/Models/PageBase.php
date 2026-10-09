<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PageBase.
 *
 * @package Modules\Sys\Repositories\Models
 */
class PageBase extends Model
{

    protected $table = 'sys_page_base';
    protected $primaryKey = 'page_id';
    public $timestamps = false;

    protected $guarded = [];
    protected $casts = [
        'page_index' => 'boolean',
        'page_gb' => 'boolean',
        'page_activity' => 'boolean',
        'page_point' => 'boolean',
        'page_gbs' => 'boolean'
    ];
}

<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PageModule.
 *
 * @package Modules\Sys\Repositories\Models
 */
class PageModule extends Model
{

    protected $table = 'sys_page_module';
    protected $primaryKey = 'pm_id';
    public $timestamps = false;

    protected $guarded = [];
    protected $casts = [
        'pm_enable' => 'boolean',
        'pm_json' => 'array'
    ];
}

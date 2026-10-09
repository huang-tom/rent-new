<?php

namespace Modules\Admin\Repositories\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Class MenuBase.
 *
 * @package namespace Modules\Admin\Repositories\Models;
 */
class MenuBase extends Model
{
    protected $table = 'admin_menu_base';
    protected $primaryKey = 'menu_id';
    public $timestamps = false;
    public $incrementing = false;
    protected $guarded = [];

    protected $casts = [
        'menu_close' => 'boolean',
        'menu_hidden' => 'boolean',
        'menu_enable' => 'boolean',
        'menu_dot' => 'boolean',
        'menu_buildin' => 'boolean'
    ];
}

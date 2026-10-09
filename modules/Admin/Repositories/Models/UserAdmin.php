<?php

namespace Modules\Admin\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserAdmin.
 *
 * @package Modules\Admin\Services\Models
 */
class UserAdmin extends Model
{

    protected $table = 'admin_user_admin';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'user_is_superadmin' => 'boolean'
    ];
}

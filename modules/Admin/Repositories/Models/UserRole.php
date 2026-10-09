<?php

namespace Modules\Admin\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserRole.
 *
 * @package Modules\Admin\Repositories\Models
 */
class UserRole extends Model
{

    protected $table = 'admin_user_role';
    protected $primaryKey = 'user_role_id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'user_role_buildin' => 'boolean'
    ];

    /**
     * 获取用户角色权限
     *
     * @return array
     */
    public function getMenuPermissions(): array
    {
        return explode(',', $this->menu_ids ?? ''); // 返回权限ID的数组
    }

    /**
     * 获取角色的名称
     */
    public function getRoleName()
    {
        return $this->user_role_name ?? '未知角色';
    }

}

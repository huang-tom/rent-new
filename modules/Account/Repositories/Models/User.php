<?php

namespace Modules\Account\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Laravel\Lumen\Auth\Authorizable;
use Modules\Admin\Repositories\Models\UserAdmin;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Class User.
 *
 * @package namespace App\Repositories\Models;
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable;

    protected $table = 'account_user_base';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'user_password'
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'user_id' => $this->user_id,
            'user_account' => $this->user_account,
            'user_salt' => $this->user_salt
        ];
    }

    /**
     * @return array 返回用户密码 和 user_salt
     */
    public function getAuthPassword()
    {
        // TODO: Implement getAuthPassword() method.
        return [$this->user_password, $this->user_salt];
    }


    /**
     * 获取用户ID
     * @return mixed
     */
    public static function getUserId()
    {
        return auth()->id() ?? 0;
    }


    /**
     * 关联 user_info 表
     */
    public function info()
    {
        return $this->hasOne(UserInfo::class, 'user_id', 'user_id');
    }


    /**
     * 关联 user_admin 表
     */
    public function admin()
    {
        return $this->hasOne(UserAdmin::class, 'user_id', 'user_id');
    }


    /**
     * 获取用户信息
     * @return AuthenticatableContract|null
     */
    public static function getUser()
    {
        $user = auth()->user();

        if ($user) {
            $user->load(['info', 'admin']);

            // 获取昵称
            $user->user_nickname = $user->info->user_nickname ?? null;
            // 用户头像
            $user->user_avatar = $user->info->user_avatar ?? null;
        }

        return $user;
    }


    /**
     * 获取是否是超级管理员属性
     *
     * @return bool
     */
    public function getIsSuperAdminAttribute(): bool
    {
        return $this->admin && $this->admin->user_is_superadmin;
    }


    /**
     * 获取是否是管理员属性
     *
     * @return bool
     */
    public function getIsAdminAttribute(): bool
    {
        return $this->admin !== null;
    }


    /**
     * 判断用户是否是超级管理员
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin;
    }


    /**
     * 判断用户是否是超级管理员
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }


    /**
     * 用户角色权限ID
     *
     * @return bool
     */
    public function userRoleId(): int
    {
        return $this->admin->user_role_id;
    }

}

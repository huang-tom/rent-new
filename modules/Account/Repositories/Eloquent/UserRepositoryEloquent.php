<?php

namespace Modules\Account\Repositories\Eloquent;

use Illuminate\Support\Facades\Hash;
use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Account\Repositories\Contracts\UserRepository;
use Modules\Account\Repositories\Models\User;

/**
 * Class UserRepositoryEloquent.
 *
 * @package namespace Modules\Account\Repositories\Eloquent;
 */
class UserRepositoryEloquent extends BaseRepository implements UserRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return User::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }


    /**
     * 设置用户密码
     * @param $user_id
     * @param $user_password
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection|mixed
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function setUserPassword($user_id, $user_password)
    {
        srand((double)microtime() * 1000000);
        $user_salt = uniqid(rand());
        $data['user_salt'] = $user_salt;
        // [本地修复] 与 LoginService::login() 的校验算法保持一致（md5(密码+salt)）。
        // 原代码用 Hash::make()（bcrypt），与登录校验的 md5 永不相等，导致
        // 管理员重置密码 / 会员修改密码后该账号永远无法登录。
        $data['user_password'] = md5($user_password . $user_salt);

        return $this->edit($user_id, $data);
    }


    //注册操作
    public function insertUser($attributes)
    {
        srand((double)microtime() * 1000000);
        $user_salt = uniqid(rand());

        $user = $this->create([
            'user_account' => $attributes['name'],
            // [本地修复] 同上：注册写入必须与登录校验算法一致，否则注册用户永远无法登录
            'user_password' => md5($attributes['password'] . $user_salt),
            'user_salt' => $user_salt
        ]);

        return $user->user_id;
    }


    /**
     * 获取用户 不能替换函数需要传对象给auth
     * @param $where
     * @return \Closure|null
     */
    public function getUser($where)
    {
        return $this->findWhere($where)->first();
    }

}


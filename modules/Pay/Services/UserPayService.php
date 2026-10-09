<?php

namespace Modules\Pay\Services;

use Illuminate\Support\Facades\Hash;
use Kuteshop\Core\Service\BaseService;
use Modules\Pay\Repositories\Contracts\UserPayRepository;
use App\Exceptions\ErrorException;

/**
 * Class UserPayService.
 *
 * @package Modules\Pay\Services
 */
class UserPayService extends BaseService
{

    public function __construct(UserPayRepository $userPayRepository)
    {
        $this->repository = $userPayRepository;
    }


    /**
     * 获取用户设置的支付密码
     * @param $user_id
     * @return array
     */
    public function getPayPassword($user_id)
    {
        return $this->repository->getOne($user_id);
    }


    /**
     * 设置更新支付密码
     * @param $old_pay_password
     * @param $new_pay_password
     * @param $pay_password
     * @param $user_id
     * @return mixed
     * @throws ErrorException
     */
    public function changePayPassword($old_pay_password, $new_pay_password, $pay_password, $user_id)
    {

        $user_pay = $this->getPayPassword($user_id);
        if (empty($user_pay)) {
            if ($new_pay_password == '' || $pay_password == '') {
                throw new ErrorException(__("密码不能为空！"));
            }

            if ($new_pay_password !== $pay_password) {
                throw new ErrorException(__("两次输入密码不一致！"));
            }

            srand((double)microtime() * 1000000);
            $user_pay_salt = uniqid(rand());
            $user_pay_passwd = Hash::make($user_pay_salt . md5($new_pay_password));

            $result = $this->repository->add([
                'user_id' => $user_id,
                'user_pay_passwd' => $user_pay_passwd,
                'user_pay_salt' => $user_pay_salt
            ]);

        } else {

            if (!empty($user_pay_row) && $old_pay_password == '') {
                throw new ErrorException(__('请输入原支付密码'));
            }

            //todo 验证原支付密码
            $user_pay_salt = $user_pay['user_pay_salt'];
            $hash_password = Hash::make($user_pay_salt . md5($old_pay_password));
            if ($hash_password !== $user_pay['user_pay_passwd']) {
                throw new ErrorException(__('原支付密码不正确！'));
            }

            //todo 验证新密码是否变更
            $tmp_pay_passwd = Hash::make($user_pay_salt . md5($new_pay_password));
            if ($tmp_pay_passwd === $user_pay['user_pay_passwd']) {
                throw new ErrorException(__("新密码不能与原密码相同！"));
            }

            srand((double)microtime() * 1000000);
            $user_pay_salt = uniqid(rand());
            $user_pay_passwd = Hash::make($user_pay_salt . md5($new_pay_password));
            $result = $this->repository->edit($user_id, [
                'user_pay_passwd' => $user_pay_passwd,
                'user_pay_salt' => $user_pay_salt
            ]);
        }

        return $result;
    }


}

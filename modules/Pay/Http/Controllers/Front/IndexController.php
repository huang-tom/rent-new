<?php

namespace Modules\Pay\Http\Controllers\Front;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Account\Repositories\Models\User;
use Modules\Pay\Services\UserPayService;

class IndexController extends BaseController
{
    private $userPayService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserPayService $userPayService)
    {
        $this->userPayService = $userPayService;
    }


    /**
     * 用户设置的支付密码
     */
    public function getPayPasswd()
    {
        $user_id = User::getUserId();
        $data = $this->userPayService->getPayPassword($user_id);
        if (!empty($data)) {
            return Respond::success($data);
        } else {
            return Respond::error();
        }
    }


    /**
     * 修改支付密码
     */
    public function changePayPassword(Request $request)
    {

        $user_id = User::getUserId();
        $old_pay_password = $request->input('old_pay_password', '');
        $new_pay_password = $request->input('new_pay_password', '');
        $pay_password = $request->input('pay_password', '');

        $success = $this->userPayService->changePayPassword($old_pay_password, $new_pay_password, $pay_password, $user_id);
        if ($success) {
            return Respond::success([]);
        }

        return Respond::error('密码修改失败');
    }


}

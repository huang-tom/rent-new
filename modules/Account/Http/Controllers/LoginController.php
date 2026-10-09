<?php

namespace Modules\Account\Http\Controllers;

use App\Exceptions\ErrorException;
use App\Support\BindConnectCode;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Services\LoginService;
use Modules\Account\Services\UserService;
use App\Support\Respond;
use Modules\Sys\Services\ConfigBaseService;
use Modules\Sys\Services\VerifyCodeService;

class LoginController extends BaseController
{
    private $userService;
    private $configBaseService;
    private $verifyCodeService;
    private $loginService;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        UserService       $userService,
        ConfigBaseService $configBaseService,
        VerifyCodeService $verifyCodeService,
        LoginService      $loginService
    )
    {
        $this->userService = $userService;
        $this->configBaseService = $configBaseService;
        $this->verifyCodeService = $verifyCodeService;
        $this->loginService = $loginService;
    }


    /**
     * 登录
     */
    public function login(Request $request)
    {
        if ($request->has('verify_code')) {
            checkVerifyCode($request);
        }
        $data = $this->loginService->login($request);

        return Respond::success($data);
    }


    //用户注册
    public function register(Request $request)
    {
        if ($request->has('verify_code')) {
            checkVerifyCode($request);
        }

        $this->loginService->register($request);
        $data = $this->loginService->login($request);

        return Respond::success($data);
    }


    /**
     * 获取协议信息
     */
    public function protocol(Request $request)
    {
        $protocols_key = $request->get('protocols_key', 'reg_protocols_description');
        $document = $this->configBaseService->getConfig($protocols_key);
        $data['document'] = $document;

        return Respond::success($data);
    }


    /**
     * 设置登录密码
     */
    public function setNewPassword(Request $request)
    {

        $user_id = 0;
        $verify_key = $request->input('verify_key', '');
        $verify_code = $request->input('verify_code', '');
        $bind_type = $request->input('bind_type', 1);

        // 验证验证码
        if (!$this->verifyCodeService->checkVerifyCode($verify_key, $verify_code)) {
            throw new ErrorException(__('验证码有误'));
        }

        // 验证新密码是否为空
        $password = $request->input('password', '');
        if ($password == '') {
            throw new ErrorException(__('请输入密码'));
        }

        // 根据绑定类型处理
        if ($bind_type == BindConnectCode::MOBILE) {

            // 验证手机号格式
            $mobile_row = $this->loginService->getMobileCountry($verify_key);
            $bind_id = sprintf("%s%d", $mobile_row['country_code'], $mobile_row['mobile']);
            $bind_row = $this->loginService->checkBindInfo($bind_id);
            $user_id = $bind_row['user_id'];

        } elseif ($bind_type == BindConnectCode::EMAIL) {

            $bind_id = $verify_key;
            $bind_row = $this->loginService->checkBindInfo($bind_id);
            $user_id = $bind_row['user_id'];

        } elseif ($bind_type == BindConnectCode::ACCOUNT) {
            // 检查用户登录状态和旧密码
            $user_id = checkLoginUserId();
            $old_password = $request->input('old_password', '');
            $this->loginService->checkUserPassword($user_id, $old_password);
        }

        // 重置密码
        $this->loginService->doResetPasswd($user_id, $password);

        return Respond::success([]);
    }


    /**
     * 手机验证码登录
     */
    public function doSmsLogin(Request $request)
    {
        $verify_key = $request->input('verify_key', '');
        $verify_code = $request->input('verify_code', '');

        // 验证验证码
        if (!$this->verifyCodeService->checkVerifyCode($verify_key, $verify_code)) {
            throw new ErrorException(__('验证码有误'));
        }

        $data = $this->loginService->doSmsLogin($verify_key);

        return Respond::success($data);
    }

}

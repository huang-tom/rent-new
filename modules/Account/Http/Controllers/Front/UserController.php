<?php

namespace Modules\Account\Http\Controllers\Front;

use App\Exceptions\ErrorException;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Criteria\UserLevelCriteria;
use Modules\Account\Services\LoginService;
use Modules\Account\Services\UserInfoService;
use App\Support\Respond;
use Modules\Account\Services\UserLevelService;
use Modules\Sys\Services\VerifyCodeService;

class UserController extends BaseController
{
    private $userInfoService;
    private $verifyCodeService;
    private $loginService;
    private $userLevelService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        UserInfoService   $userInfoService,
        VerifyCodeService $verifyCodeService,
        LoginService      $loginService,
        UserLevelService  $userLevelService
    )
    {
        $this->userInfoService = $userInfoService;
        $this->verifyCodeService = $verifyCodeService;
        $this->loginService = $loginService;
        $this->userLevelService = $userLevelService;
    }


    /**
     * 获取用户基本信息
     */
    public function info()
    {
        $data = $this->userInfoService->getUserInfo();

        return Respond::success($data);
    }


    /**
     * 修改用户基本信息
     */
    public function edit(Request $request)
    {
        $user_id = checkLoginUserId();
        $this->userInfoService->editUserInfo($user_id, $request);

        return Respond::success($request->all());
    }


    /**
     * 绑定手机号
     */
    public function bindMobile(Request $request)
    {

        $user_id = checkLoginUserId();
        $verify_key = $request->input('verify_key', '');
        $verify_code = $request->input('verify_code', '');

        // 验证验证码
        if (!$this->verifyCodeService->checkVerifyCode($verify_key, $verify_code)) {
            throw new ErrorException(__('验证码有误'));
        }

        $row = $this->loginService->getMobileCountry($verify_key);
        $result = $this->loginService->bindMobile($user_id, $row['country_code'], $row['mobile']);

        return Respond::success($result);
    }


    /**
     * 解绑手机
     */
    public function unBindMobile(Request $request)
    {
        $user_id = checkLoginUserId();
        $verify_key = $request->input('verify_key', '');
        $verify_code = $request->input('verify_code', '');

        // 验证验证码
        if (!$this->verifyCodeService->checkVerifyCode($verify_key, $verify_code)) {
            throw new ErrorException(__('验证码有误'));
        }

        $row = $this->loginService->getMobileCountry($verify_key);
        $this->loginService->unBindMobile($user_id, $row['country_code'], $row['mobile']);

        return Respond::success($row);
    }


    /**
     * 提交实名认证信息
     */
    public function saveCertificate(Request $request)
    {
        $user_id = checkLoginUserId();
        $row = $request->all();
        $data = $this->loginService->saveCertificate($user_id, $row);

        return Respond::success($data);
    }

    public function getCompanyByUserId(Request $request)
    {
        $data = [];

        return Respond::success($data);
    }


    /**
     * 会员等级
     */
    function listBaseUserLevel(Request $request)
    {
        $data = $this->userLevelService->list($request, new UserLevelCriteria($request));

        return Respond::success($data);
    }


    public function listsExpRule()
    {
        $data = $this->loginService->listsExpRule();

        return Respond::success($data);
    }

}

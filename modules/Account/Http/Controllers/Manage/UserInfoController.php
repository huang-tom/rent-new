<?php

namespace Modules\Account\Http\Controllers\Manage;

use App\Support\BindConnectCode;
use App\Support\Respond;
use App\Support\SimpleXlsx;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Criteria\UserInfoCriteria;
use Modules\Account\Services\LoginService;
use Modules\Account\Services\UserInfoService;

class UserInfoController extends BaseController
{
    private $userInfoService;
    private $loginService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserInfoService $userInfoService, LoginService $loginService)
    {
        $this->userInfoService = $userInfoService;
        $this->loginService = $loginService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->userInfoService->list($request, new UserInfoCriteria($request));

        return Respond::success($data);
    }


    /**
     * 会员信息
     */
    public function getUserData(Request $request)
    {
        // [本地修复] 两处调用方语义不同：
        //   1) 会员管理列表点某行 → 显式带 user_id，取该会员资料；
        //   2) 右上角「个人中心 → 基本信息」→ 不带 user_id，要的是"当前登录人"的资料。
        // 原实现把 null 直接交给 getOne()，返回空数组，导致个人中心基本信息整页空白
        // （账号/昵称/手机号/邮箱全为空）。此处无 user_id 时回退为当前登录用户。
        $user_id = $request->get('user_id') ?: auth()->id();
        $data = $this->userInfoService->getUserData($user_id);

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        // 设置新的参数
        $request->merge(['bind_type' => BindConnectCode::ACCOUNT]);
        $data = $this->loginService->register($request);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        // [本地修复] 与 getUserData 同理：个人中心保存时不传 user_id，
        // 原默认值 -1 会让保存落到一个不存在的用户上（静默失败、页面无任何提示）。
        $user_id = $request->get('user_id') ?: auth()->id();

        // [本地修复] 缺省值不再写死，而是沿用该用户当前的值。
        // 个人中心只提交昵称/头像/手机号/性别/生日/邮箱/标签，若其余字段仍用
        // 硬编码默认值，会把用户等级、认证状态、来源等重置掉。
        $exists = $this->userInfoService->getUserData($user_id);
        $exists = is_array($exists) ? $exists : [];

        $user_info_row = [
            'user_nickname' => $request->input('user_nickname', $exists['user_nickname'] ?? ''), //昵称
            'user_avatar' => $request->input('user_avatar', $exists['user_avatar'] ?? ''), //用户头像
            'user_state' => $request->input('user_state', $exists['user_state'] ?? 1), //状态(ENUM):0-锁定;1-已激活;2-未激活;
            'user_mobile' => $request->input('user_mobile', $exists['user_mobile'] ?? ''), //手机号码
            'user_intl' => $request->input('user_intl', $exists['user_intl'] ?? '+86'), //国家编码
            'user_gender' => $request->input('user_gender', $exists['user_gender'] ?? 1), //性别(ENUM):0-保密;1-男;  2-女;
            'user_birthday' => $request->input('user_birthday', $exists['user_birthday'] ?? ''), //生日(DATE)
            'user_email' => $request->input('user_email', $exists['user_email'] ?? ''), //用户邮箱(email)
            'user_level_id' => $request->input('user_level_id', $exists['user_level_id'] ?? 0), //等级编号
            'user_is_authentication' => $request->input('user_is_authentication', $exists['user_is_authentication'] ?? 0), //认证状态(ENUM):0-未认证;1-待审核;2-认证通过;3-认证失败
            'tag_ids' => $request->input('tag_ids', $exists['tag_ids'] ?? ''), //用户标签(DOT)
            'user_from' => $request->input('user_from', $exists['user_from'] ?? 2310), //用户来源(ENUM):2310-其它;2311-pc;2312-H5;2313-APP;2314-小程序;2315-公众号
            'user_new' => $request->input('user_new', $exists['user_new'] ?? 1) //新人标识(BOOL):0-不是;1-是
        ];
        $data = $this->userInfoService->edit($user_id, $user_info_row);

        return Respond::success($data);
    }


    /*
     * 修改密码
     */
    public function passWordEdit(Request $request)
    {
        $user_id = $request->input('user_id');
        $user_password = $request->input('user_password');

        $success = $this->userInfoService->passWordEdit($user_id, $user_password);
        if ($success) {
            return Respond::success(['user_id' => $user_id], __('密码修改成功'));
        }

        return Respond::error(__('密码修改失败'));
    }


    /*
     * 修改自己的登录密码（右上角个人中心）
     * 需校验原密码；user_id 取自当前登录态，不接受前端传入，防止越权改他人密码
     */
    public function myPassWordEdit(Request $request)
    {
        $user_id = auth()->id();
        $old_password = (string)$request->input('old_password', '');
        $user_password = (string)$request->input('user_password', '');

        if (!$user_id) {
            return Respond::error(__('请先登录'));
        }

        if ($old_password === '') {
            return Respond::error(__('请输入原密码'));
        }

        if (strlen($user_password) < 6) {
            return Respond::error(__('新密码长度不能少于6位'));
        }

        if ($user_password === $old_password) {
            return Respond::error(__('新密码不能与原密码相同'));
        }

        // 校验原密码，错误时 loginService 会抛出异常由全局处理器转为错误响应
        $this->loginService->checkUserPassword($user_id, $old_password);

        $success = $this->userInfoService->passWordEdit($user_id, $user_password);
        if ($success) {
            return Respond::success(['user_id' => $user_id], __('密码修改成功'));
        }

        return Respond::error(__('密码修改失败'));
    }


    public function remove(Request $request)
    {
        $user_id = $request->input('user_id', 0);
        $data = $this->userInfoService->removeUser($user_id);

        return Respond::success($data);
    }


    /**
     * 状态变更（实名认证审核 / 账号启用停用）
     *
     * [新增 2026-09-23] 补前端一直在调、后端却缺失的路由（原为 404）：
     *   views/account/userInfo/components/UserInfo.vue:120/126 的「认证通过 / 认证失败」，
     *   以及 index.vue 里残留的 handleState。
     */
    public function editState(Request $request)
    {
        $data = $this->userInfoService->editState($request);

        return Respond::success($data);
    }


    /**
     * 批量设置会员标签
     *
     * [新增 2026-09-23] 补 AddTags.vue 调用的 /manage/account/userInfo/addTags（原为 404）
     */
    public function addTags(Request $request)
    {
        $data = $this->userInfoService->addTags($request);

        return Respond::success($data, __('设置标签成功'));
    }


    /**
     * 批量发放优惠券
     *
     * [新增 2026-09-23] 补 AddVouchers.vue 调用的 /manage/account/userInfo/addVouchers（原为 404）
     */
    public function addVouchers(Request $request)
    {
        $data = $this->userInfoService->addVouchers($request);

        $msg = __('发放成功') . $data['success_num'] . __('人');
        if ($data['fail_num'] > 0) {
            $msg .= '，' . __('失败') . $data['fail_num'] . __('人');
        }

        return Respond::success($data, $msg);
    }


    /**
     * 导出会员列表（xlsx）
     *
     * [新增 2026-09-23] 补 index.vue 工具栏「导出」调用的 /manage/account/userInfo/exportFile（原为 404）。
     * 走 SimpleXlsx（零依赖手写 xlsx，见 app/Support/SimpleXlsx.php）——
     * 项目没有装任何 Excel 库，而前端下载文件名写死 .xlsx，吐 CSV 会被 Excel 报格式不符。
     */
    public function exportFile(Request $request)
    {
        $user_ids = $request->input('user_ids', '');
        $user_ids = is_array($user_ids) ? $user_ids : explode(',', (string)$user_ids);
        $user_ids = array_values(array_unique(array_filter(array_map('intval', $user_ids))));

        [$header, $rows] = $this->userInfoService->exportRows($user_ids);

        $xlsx = new SimpleXlsx(__('会员列表'));
        $xlsx->addHeader($header);
        foreach ($rows as $row) {
            $xlsx->addRow($row);
        }

        return $xlsx->download(__('会员列表') . '_' . date('YmdHis') . '.xlsx');
    }


    /**
     * 下载会员导入模板（xlsx）
     *
     * [新增 2026-09-23] 补 /manage/account/userInfo/exportTemp（原为 404）。
     * ⚠️ 配套的**导入**接口在本项目里并不存在（前端 UserInfoImport.vue 的 action 也缺少对应后端路由），
     *    所以这里只保证"模板能下载、列名可用"，真正启用导入前必须先补导入接口并让列名与之一一对应。
     */
    public function exportTemp(Request $request)
    {
        $xlsx = new SimpleXlsx(__('会员导入模板'));
        $xlsx->addHeader(['账号', '昵称', '手机号', '邮箱', '性别(男/女/保密)', '会员等级编号']);
        $xlsx->addRow(['demo001', '示例会员', '13800000000', 'demo@example.com', '男', 1]);

        return $xlsx->download(__('会员导入模板') . '.xlsx');
    }


}

<?php

namespace Modules\Account\Services;

use App\Exceptions\ErrorException;
use App\Support\StateCode;
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserBindConnectRepository;
use Modules\Account\Repositories\Contracts\UserDeliveryAddressRepository;
use Modules\Account\Repositories\Contracts\UserDistributionRepository;
use Modules\Account\Repositories\Contracts\UserInfoRepository;
use Modules\Account\Repositories\Contracts\UserLevelRepository;
use Modules\Account\Repositories\Contracts\UserLoginRepository;
use Modules\Account\Repositories\Contracts\UserMessageRepository;
use Modules\Account\Repositories\Contracts\UserRepository;
use Modules\Account\Repositories\Contracts\UserTagBaseRepository;
use Modules\Account\Repositories\Contracts\UserTagGroupRepository;
use Modules\Account\Repositories\Models\User;
use Modules\Account\Repositories\Models\UserInfo;
use Modules\Account\Repositories\Models\UserLevel;
use Modules\Account\Repositories\Models\UserLogin;
use Modules\Admin\Repositories\Contracts\MenuBaseRepository;
use Modules\Admin\Repositories\Contracts\UserAdminRepository;
use Modules\Admin\Repositories\Contracts\UserRoleRepository;
use Modules\Analytics\Repositories\Models\AnalyticsOrder;
use Modules\Analytics\Repositories\Models\AnalyticsTrade;
use Modules\Pay\Repositories\Contracts\DistributionCommissionRepository;
use Modules\Pay\Repositories\Contracts\UserResourceRepository;
use Modules\Pay\Repositories\Models\UserResource;
use Modules\Shop\Repositories\Contracts\UserFavoritesItemRepository;
use Modules\Shop\Repositories\Contracts\UserVoucherRepository;
use Modules\Shop\Services\UserVoucherService;
use Modules\Trade\Repositories\Contracts\DistributionOrderRepository;
use Modules\Trade\Repositories\Contracts\OrderInfoRepository;

class UserInfoService extends BaseService
{

    private $userRepository;
    private $userAdminRepository;
    private $userResourceRepository;
    private $userRoleRepository;
    private $menuBaseRepository;
    private $userLevelRepository;
    private $userTagBaseRepository;
    private $userTagGroupRepository;
    private $userDeliveryAddressRepository;
    private $userLoginRepository;
    private $userDistributionRepository;
    private $distributionCommissionRepository;
    private $distributionOrderRepository;
    private $orderInfoRepository;
    private $userFavoritesItemRepository;
    private $userMessageRepository;
    private $userBindConnectRepository;
    private $userVoucherRepository;
    /** @var UserVoucherService 批量发券直接复用 Shop 模块现成的发券逻辑（含库存/限领/等级校验） */
    private $userVoucherService;

    private $analyticsOrder;
    private $analyticsTrade;

    public function __construct(
        UserInfoRepository               $userInfoRepository,
        UserRepository                   $userRepository,
        UserResourceRepository           $userResourceRepository,
        UserAdminRepository              $userAdminRepository,
        UserRoleRepository               $userRoleRepository,
        MenuBaseRepository               $menuBaseRepository,
        UserVoucherRepository            $userVoucherRepository,
        UserLevelRepository              $userLevelRepository,
        UserTagBaseRepository            $userTagBaseRepository,
        UserTagGroupRepository           $userTagGroupRepository,
        UserDeliveryAddressRepository    $userDeliveryAddressRepository,
        UserLoginRepository              $userLoginRepository,
        UserDistributionRepository       $userDistributionRepository,
        DistributionCommissionRepository $distributionCommissionRepository,
        DistributionOrderRepository      $distributionOrderRepository,
        OrderInfoRepository              $orderInfoRepository,
        UserFavoritesItemRepository      $userFavoritesItemRepository,
        UserMessageRepository            $userMessageRepository,
        UserBindConnectRepository        $userBindConnectRepository,

        UserVoucherService               $userVoucherService,


        AnalyticsOrder                   $analyticsOrder,
        AnalyticsTrade                   $analyticsTrade
    )
    {
        $this->repository = $userInfoRepository;
        $this->userRepository = $userRepository;
        $this->userResourceRepository = $userResourceRepository;
        $this->userAdminRepository = $userAdminRepository;
        $this->userRoleRepository = $userRoleRepository;
        $this->menuBaseRepository = $menuBaseRepository;
        $this->userVoucherRepository = $userVoucherRepository;
        $this->userLevelRepository = $userLevelRepository;
        $this->userTagBaseRepository = $userTagBaseRepository;
        $this->userTagGroupRepository = $userTagGroupRepository;
        $this->userDeliveryAddressRepository = $userDeliveryAddressRepository;
        $this->userLoginRepository = $userLoginRepository;
        $this->userDistributionRepository = $userDistributionRepository;
        $this->distributionCommissionRepository = $distributionCommissionRepository;
        $this->distributionOrderRepository = $distributionOrderRepository;
        $this->orderInfoRepository = $orderInfoRepository;
        $this->userFavoritesItemRepository = $userFavoritesItemRepository;
        $this->userMessageRepository = $userMessageRepository;
        $this->userBindConnectRepository = $userBindConnectRepository;

        $this->userVoucherService = $userVoucherService;

        $this->analyticsOrder = $analyticsOrder;
        $this->analyticsTrade = $analyticsTrade;
    }


    /**
     * 获取登录用户信息
     * @return array
     * @throws ErrorException
     */
    public function getLoginUser()
    {
        $load = auth()->payload();
        $user_id = $load->get('user_id');
        if (!$user_id) {
            throw new ErrorException(__("Token失效，请重新登录"));
        }

        return [
            'user_id' => $user_id,
            'user_account' => $load->get('user_account'),
            'user_salt' => $load->get('user_salt')
        ];
    }


    /**
     * getUserInfo
     * @return array|mixed
     * @throws ErrorException
     */
    public function getUserInfo()
    {
        $login_user = $this->getLoginUser();
        $user_id = $login_user['user_id'];
        $user_base = $this->userRepository->getOne($user_id);

        if (empty($user_base)) {
            throw new ErrorException(__("账号不存在"));
        }

        //todo 判断token有效性
        if ($user_base['user_salt'] !== $login_user['user_salt']) {
            throw new ErrorException("Token失效，请重新登录");
        }

        //todo 获取userInfo信息
        $data = $this->repository->getOne($user_id);
        if ($data && $data['user_state'] == 0) {
            throw new ErrorException(__("您的账号已被禁用,请联系管理员"));
        }
        $data['user_idcard_image_list'] = explode(',', $data['user_idcard_images']);

        //todo 获取用户资产信息
        $user_resource = $this->userResourceRepository->getOne($user_id);
        if ($user_resource) {
            $data = array_merge($data, $user_resource);
            $data['user_points'] = round($user_resource['user_points'], 2);
        }

        //todo 用户管理表
        $admin_data = $this->getUserPermissions($user_id);
        if (!empty($admin_data)) {
            $data['roles'] = $admin_data['roles'];
            $data['permissions'] = $admin_data['permissions'];
        }

        //todo 获取用户未使用优惠券数量
        $data['voucher'] = $this->userVoucherRepository->getNum(['user_id' => $user_id, 'voucher_state_id' => StateCode::VOUCHER_STATE_UNUSED]);

        //佣金
        $data['commission_amount'] = 0;
        $distribution_commission_row = $this->distributionCommissionRepository->getOne($user_id);
        if ($distribution_commission_row) {
            $data['commission_amount'] = $distribution_commission_row['commission_amount'];
        }

        //待付款订单数量
        $data['wait_pay_num'] = $this->orderInfoRepository->getNum([
            'user_id' => $user_id,
            'order_state_id' => StateCode::ORDER_STATE_WAIT_PAY
        ]);

        //收藏数量
        $data['favorites_goods_num'] = $this->userFavoritesItemRepository->getNum(['user_id' => $user_id]);

        //未读消息数量
        $data['unread_number'] = $this->userMessageRepository->getNum(['user_id' => $user_id, 'message_is_read' => 0]);

        return $data;
    }


    /**
     * 获取用户当前角色权限
     * @param $user_id
     * @return array
     */
    public function getUserPermissions($user_id = null)
    {
        $data = [];

        $admin_user = $this->userAdminRepository->getOne($user_id);
        if (!empty($admin_user)) {
            //todo 获取用户的角色权限菜单
            $user_role_id = $admin_user['user_role_id'];
            $user_role_menu = $this->userRoleRepository->getOne($user_role_id);
            if (!empty($user_role_menu)) {
                $data['roles'] = [$user_role_menu['user_role_code']];

                //todo 获取菜单 menu_permission
                $menu_ids = explode(',', $user_role_menu['menu_ids']);
                $menu_rows = $this->menuBaseRepository->gets($menu_ids);
                $permission_rows = [];
                foreach ($menu_rows as $menu_row) {
                    if ($menu_row['menu_permission'] != '' && !in_array($menu_row['menu_permission'], $permission_rows)) {
                        $permission_rows[] = $menu_row['menu_permission'];
                    }
                }

                $data['permissions'] = $permission_rows;
            }
        }

        return $data;
    }


    /**
     * getUserData
     * @param $user_id
     * @return array|mixed
     */
    public function getUserData($user_id = null)
    {
        $user_data = [];

        // 用户基本信息
        $user_base = $this->userRepository->getOne($user_id);
        if (!empty($user_base)) {
            $user_data = $user_base;
            // [本地修复] account_user_base 整行被返回，其中 user_salt 是配合
            // md5(密码+salt) 做校验用的盐值，前端任何地方都用不到，属无用泄露，直接剔除。
            // （user_password 因模型 hidden 不会出现在响应里，salt 也没必要出现。）
            unset($user_data['user_salt'], $user_data['user_password']);
        }

        // 用户详情信息
        $user_info = $this->repository->getOne($user_id);
        if (!empty($user_info)) {
            $user_data = array_merge($user_data, $user_info);

            // 身份证图片
            if ($user_info['user_idcard_images']) {
                $user_data['user_idcard_image_list'] = explode(',', $user_info['user_idcard_images']);
            }

            // 用户等级
            $user_level = $this->userLevelRepository->getOne($user_info['user_level_id']);
            if (!empty($user_level)) {
                $user_data['user_level_name'] = $user_level['user_level_name'];
            }

            // 用户标签和分组
            if ($user_info['tag_ids']) {
                $tag_ids = explode(',', $user_info['tag_ids']);
                $tag_rows = $this->userTagBaseRepository->gets($tag_ids);

                if (!empty($tag_rows)) {
                    $tag_title_rows = array_column($tag_rows, 'tag_title');
                    $user_data['tag_titles'] = implode('、', $tag_title_rows);

                    $tag_group_ids = array_column($tag_rows, 'tag_group_id');
                    $tag_group_rows = $this->userTagGroupRepository->gets($tag_group_ids);
                    if (!empty($tag_group_rows)) {
                        $group_name_rows = array_column($tag_group_rows, 'tag_group_name');
                        $user_data['tag_group_names'] = implode('、', $group_name_rows);
                    }
                }
            }
        }

        // 本月订单统计
        $order_state_ids = [
            StateCode::ORDER_STATE_WAIT_PAY,
            StateCode::ORDER_STATE_WAIT_PAID,
            StateCode::ORDER_STATE_WAIT_REVIEW,
            StateCode::ORDER_STATE_WAIT_FINANCE_REVIEW,
            StateCode::ORDER_STATE_PICKING,
            StateCode::ORDER_STATE_WAIT_SHIPPING,
            StateCode::ORDER_STATE_SHIPPED,
            StateCode::ORDER_STATE_RECEIVED,
            StateCode::ORDER_STATE_FINISH,
            StateCode::ORDER_STATE_SELF_PICKUP
        ];
        $month_range = getMonth();
        $month_order_num = $this->analyticsOrder->getOrderNum($month_range['start'], $month_range['end'], $order_state_ids, [], $user_id);
        $user_data['month_order'] = $month_order_num;
        // 总计订单数目
        $total_order_num = $this->analyticsOrder->getOrderNum(0, 0, $order_state_ids, [], $user_id);

        $user_data['total_order'] = $total_order_num;

        // 本月消费金额
        $user_data['month_trade'] = $this->analyticsTrade->getTradeAmount($month_range['start'], $month_range['end'], [], [], $user_id);
        // 总消费金额
        $user_data['total_trade'] = $this->analyticsTrade->getTradeAmount(0, 0, [], [], $user_id);;

        // 用户地址
        $address = $this->userDeliveryAddressRepository->getOne($user_id);
        if ($address) {
            $user_data['ud_address'] = $address['ud_province'] . $address['ud_city'] . $address['ud_county'] . $address['ud_address'];
        }

        // 用户资源
        $user_resource = $this->userResourceRepository->getOne($user_id);
        if ($user_resource) {
            $user_data = array_merge($user_data, $user_resource);
        }

        // 用户登录信息
        $user_login = $this->userLoginRepository->getOne($user_id);
        if ($user_login) {
            $user_data['user_reg_time'] = $user_login['user_reg_time'];
            $user_data['user_login_time'] = $user_login['user_lastlogin_time'];
        }

        // 推广员信息
        $user_distribution = $this->userDistributionRepository->getOne($user_id);
        if ($user_distribution) {
            $user_data['user_parent_id'] = $user_distribution['user_parent_id'];
        }

        // 累计佣金
        $user_data['user_commission_now'] = 0;
        $distribution_commission = $this->distributionCommissionRepository->getOne($user_id);
        if ($distribution_commission) {
            $user_data['user_commission_now'] = $distribution_commission['commission_amount'] - $distribution_commission['commission_settled'];
        }

        // 本月佣金
        $user_data['month_commission_buy'] = 0;
        $month_commission_orders = $this->distributionOrderRepository->find([
            'user_id' => $user_id,
            ['uo_time', '>=', $month_range['start']],
            ['uo_time', '<=', $month_range['end']]
        ]);
        if (!empty($month_commission_orders)) {
            $user_data['month_commission_buy'] = array_sum(array_column($month_commission_orders, 'uo_buy_commission'));
        }

        return $user_data;
    }


    /*
     * passWordEdit
     * @param $user_id
     * @param $user_password
     */
    public function passWordEdit($user_id = 0, $user_password = '')
    {
        if (!$user_id) {
            throw new ErrorException(__("用户Id不能为空"));
        }

        if (!$user_password) {
            throw new ErrorException(__("密码不能为空"));
        }

        try {
            $result = $this->userRepository->setUserPassword($user_id, $user_password);

            return $result;
        } catch (\Exception $e) {
            throw new ErrorException(__("密码修改失败"));
        }

    }


    /**
     * 用户修改基本信息
     * @param $user_id
     * @param $request
     * @return mixed
     */
    public function editUserInfo($user_id, $request)
    {
        $result = $this->repository->edit($user_id, [
            'user_nickname' => $request->input('user_nickname', ''),
            'user_avatar' => $request->input('user_avatar', ''),
            'user_email' => $request->input('user_email', ''),
            'user_birthday' => $request->input('user_birthday', ''),
        ]);

        return $result;
    }


    /**
     * 删除用户账号
     *
     * @param int $user_id
     * @return bool
     * @throws ErrorException
     */
    public function removeUser(int $user_id = 0)
    {
        $user_admin = $this->userAdminRepository->getOne($user_id);
        if (!empty($user_admin) && $user_admin['user_is_superadmin']) {
            throw new ErrorException(__("该账号为系统管理员，不可删除！"));
        }
        DB::beginTransaction();

        try {
            $this->userRepository->remove($user_id);
            $this->repository->remove($user_id);
            $this->userLoginRepository->remove($user_id);
            $this->userResourceRepository->remove($user_id);

            // 删除用户绑定连接
            $this->userBindConnectRepository->removeWhere(['user_id' => $user_id]);


            // 分销相关用户来源关系
            $user_distribution = $this->userDistributionRepository->getOne($user_id);
            if (!empty($user_distribution)) {
                if (!$this->userDistributionRepository->remove($user_id)) {
                    throw new ErrorException(__("删除粉丝信息失败！"));
                }

                // 修改上级用户粉丝数量
                $parent_distribution = $this->userDistributionRepository->getOne($user_distribution['user_parent_id']);
                if (!empty($parent_distribution)) {
                    $fans_num = max($parent_distribution['user_fans_num'] - 1, 0);
                    if (!$this->userDistributionRepository->edit($user_distribution['user_parent_id'], ['user_fans_num' => $fans_num])) {
                        throw new ErrorException(__("修改粉丝数量失败！"));
                    }
                }
            }

            // 删除推广粉丝产生的佣金汇总表
            //$this->distributionGeneratedCommissionRepository->removeWhere(['user_id' => $user_id]);

            // 删除讲师

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('删除用户操作失败: ') . $e->getMessage());
        }

        return true;
    }


    /**
     * 绑定用户信息
     * @param array $items
     * @param array $map
     * @param string $key_name
     * @return array
     */
    public function fixUserInfo(array $items = [], array $map = ["user_nickname" => "user_nickname"], string $key_name = "user_id"): array
    {
        return $this->repository->fixUserInfo($items, $map, $key_name);
    }


    /**
     * 会员状态变更（实名认证审核 / 账号启用停用）
     *
     * [新增 2026-09-23] 前端一直都在调 POST /manage/account/userInfo/editState，后端没有这条路由 → 404。
     * 调用方有两处，语义不同，这里一并支持：
     *   1) views/account/userInfo/components/UserInfo.vue:120/126
     *      「认证通过」→ {user_id, user_is_authentication: 2}
     *      「认证失败」→ {user_id, user_is_authentication: 3}
     *      —— 即实名认证的人工审核结果回写（account_user_info.user_is_authentication）。
     *   2) 其它页面可能传 {user_id, user_state}，用于启用/锁定账号
     *      （account_user_info.user_state，枚举 0-锁定 / 1-已激活 / 2-未激活）。
     *
     * 两个字段都**只在请求显式带了才改**：前端基本是只传一个字段，
     * 若无脑取默认值会把另一个字段重置掉（例如审核认证时把 user_state 抹成 0 导致账号被锁）。
     *
     * @param $request
     * @return bool
     * @throws ErrorException
     */
    public function editState($request)
    {
        $user_id = (int)$request->input('user_id', 0);
        if (!$user_id) {
            throw new ErrorException(__('用户Id不能为空'));
        }

        $state_row = [];
        if ($request->has('user_is_authentication')) {
            $auth = (int)$request->input('user_is_authentication');
            if (!in_array($auth, [0, 1, 2, 3], true)) {
                throw new ErrorException(__('认证状态取值有误'));
            }
            $state_row['user_is_authentication'] = $auth;
        }

        if ($request->has('user_state')) {
            $state = (int)$request->input('user_state');
            if (!in_array($state, [0, 1, 2], true)) {
                throw new ErrorException(__('账号状态取值有误'));
            }
            $state_row['user_state'] = $state;
        }

        if (empty($state_row)) {
            throw new ErrorException(__('没有需要变更的状态'));
        }

        $row = $this->repository->getOne($user_id);
        if (empty($row)) {
            throw new ErrorException(__('会员不存在'));
        }

        $result = $this->repository->edit($user_id, $state_row);
        if (!$result) {
            throw new ErrorException(__('状态变更失败'));
        }

        return true;
    }


    /**
     * 批量设置会员标签
     *
     * [新增 2026-09-23] 对应 views/account/userInfo/components/AddTags.vue 的「批量设置标签」。
     * 前端传 {tag_ids: "1,2,3", user_ids: "1,2,3"}（两个都是逗号串，见 AddTags.vue:96
     * `state.form.tag_ids = state.form.tag_ids.join()`）。语义是**覆盖设置**而非追加，
     * 与弹窗标题「批量设置标签」一致 —— 追加语义会让用户没法取消已打的标签。
     *
     * @param $request
     * @return bool
     * @throws ErrorException
     */
    public function addTags($request)
    {
        $user_ids = $this->parseIds($request->input('user_ids', ''));
        if (empty($user_ids)) {
            throw new ErrorException(__('请先选择会员'));
        }

        $tag_ids = $this->parseIds($request->input('tag_ids', ''));

        // 标签必须真实存在，否则会在 account_user_info.tag_ids 里塞入悬空 ID，
        // 之后 getUserData() 里的 tag_title 拼接会拿到空值、前端显示为"有标签但没名字"。
        if (!empty($tag_ids)) {
            $tag_rows = $this->userTagBaseRepository->gets($tag_ids);
            $exist_ids = array_column($tag_rows, 'tag_id');
            $diff = array_diff($tag_ids, $exist_ids);
            if (!empty($diff)) {
                throw new ErrorException(sprintf(__('标签不存在：%s'), implode(',', $diff)));
            }
        }

        DB::beginTransaction();
        try {
            // 用 Model 显式 whereIn 更新，不依赖混淆过的 BaseRepository::edits()
            // —— 那 4 个 core 基类被混淆过，行为不透明（见排查报告），显式写法完全可控。
            UserInfo::whereIn('user_id', $user_ids)->update(['tag_ids' => implode(',', $tag_ids)]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('设置标签失败: ') . $e->getMessage());
        }

        return true;
    }


    /**
     * 批量发放优惠券
     *
     * [新增 2026-09-23] 对应 views/account/userInfo/components/AddVouchers.vue 的「批量发放优惠券」。
     * 前端传 {activity_id, user_ids: "1,2,3"}。
     *
     * 实现上**直接复用 Shop\UserVoucherService::addVoucher()**，而不是自己往 shop_user_voucher 插行。
     * 原因：发券远不止"插一条记录"——addVoucher 里还含
     *   活动状态校验 / voucher_quantity 库存扣减 / 每人限领 voucher_pre_quantity /
     *   会员等级白名单 / 积分兑换扣分 / 库存归零后把活动置为已结束。
     * 这些规则一旦绕过，就会出现"券已发出、活动还剩 0 张"这类对不上的库存。
     *
     * 逐个用户处理，单个失败只记录不中断（批量发券里某几个人不满足条件属正常），
     * 最后把失败原因一并返回给前端。
     *
     * @param $request
     * @return array
     * @throws ErrorException
     */
    public function addVouchers($request)
    {
        $activity_id = (int)$request->input('activity_id', 0);
        if (!$activity_id) {
            throw new ErrorException(__('请选择优惠券'));
        }

        $user_ids = $this->parseIds($request->input('user_ids', ''));
        if (empty($user_ids)) {
            throw new ErrorException(__('请先选择会员'));
        }

        $success = 0;
        $fail = [];
        foreach ($user_ids as $user_id) {
            try {
                $this->userVoucherService->addVoucher($activity_id, $user_id);
                $success++;
            } catch (\Throwable $e) {
                $fail[] = sprintf('会员 %d：%s', $user_id, $e->getMessage());
            }
        }

        if ($success === 0) {
            throw new ErrorException(__('发放失败：') . implode('；', $fail));
        }

        return [
            'success_num' => $success,
            'fail_num' => count($fail),
            'fail_msg' => $fail,
        ];
    }


    /**
     * 导出会员列表数据（供 Controller 组装 xlsx）
     *
     * @param array $user_ids 为空则导出全部（上限 EXPORT_MAX_ROWS 行，防止一次性把内存打满）
     * @return array [表头, 数据行]
     */
    public function exportRows(array $user_ids = []): array
    {
        $header = [
            '会员编号', '账号', '昵称', '手机号', '邮箱', '性别',
            '会员等级', '余额', '积分', '注册时间', '最后登录时间',
        ];

        $rows = [];
        // 直接用 Model 显式查询，不走混淆过的 BaseRepository：
        // 一来 whereIn / limit / orderBy 的行为完全可控，二来不会因为基类的隐式 scope 而少捞数据。
        $query = UserInfo::query();
        if (!empty($user_ids)) {
            $query->whereIn('user_id', $user_ids);
        }
        $items = $query->orderBy('user_id', 'DESC')->limit(self::EXPORT_MAX_ROWS)->get()->toArray();

        if (empty($items)) {
            return [$header, $rows];
        }

        // 一次把关联数据捞全，避免逐行查库（导出几万行时 N+1 很致命）
        $ids = array_column($items, 'user_id');

        $user_base_rows = [];
        foreach (User::whereIn('user_id', $ids)->get()->toArray() as $ub) {
            $user_base_rows[$ub['user_id']] = $ub;
        }
        $resource_rows = [];
        foreach (UserResource::whereIn('user_id', $ids)->get()->toArray() as $ur) {
            $resource_rows[$ur['user_id']] = $ur;
        }
        $login_rows = [];
        foreach (UserLogin::whereIn('user_id', $ids)->get()->toArray() as $ul) {
            $login_rows[$ul['user_id']] = $ul;
        }
        $level_rows = [];
        foreach (UserLevel::query()->get()->toArray() as $lv) {
            $level_rows[$lv['user_level_id']] = $lv['user_level_name'];
        }

        $gender_map = [0 => '保密', 1 => '男', 2 => '女'];

        foreach ($items as $item) {
            $uid = $item['user_id'];
            $base = $user_base_rows[$uid] ?? [];
            $res = $resource_rows[$uid] ?? [];
            $login = $login_rows[$uid] ?? [];

            $rows[] = [
                $uid,
                $base['user_account'] ?? '',
                $base['user_nickname'] ?? ($item['user_nickname'] ?? ''),
                $base['user_mobile'] ?? '',
                $base['user_email'] ?? '',
                $gender_map[$item['user_gender'] ?? 0] ?? '',
                $level_rows[$item['user_level_id'] ?? 0] ?? '',
                $res['user_money'] ?? '0.00',
                $res['user_points'] ?? '0.00',
                $login['user_reg_time'] ?? '',
                $login['user_lastlogin_time'] ?? '',
            ];
        }

        return [$header, $rows];
    }


    /**
     * 批量接口共用的 ID 解析：兼容「逗号串」与「数组」两种入参。
     * 前端拼串（m.join()）是主用法，但后台脚本/联调时常常直接给数组，
     * 这里统一收敛，避免某个接口因为入参形态不同而行为不一致。
     */
    private function parseIds($raw): array
    {
        if (is_array($raw)) {
            $ids = $raw;
        } else {
            $ids = explode(',', (string)$raw);
        }
        return array_values(array_unique(array_filter(array_map('intval', $ids))));
    }


    /** 导出单次最大行数：够用且不会把 PHP 内存打爆 */
    const EXPORT_MAX_ROWS = 20000;


}

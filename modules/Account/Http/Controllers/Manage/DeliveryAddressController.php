<?php

namespace Modules\Account\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Criteria\UserDeliveryAddressCriteria;
use Modules\Account\Repositories\Validators\UserDeliveryAddressValidator;
use Modules\Account\Services\UserDeliveryAddressService;

/**
 * Class DeliveryAddressController.
 *
 * [新增 2026-09-23] 管理端会员收货地址控制器。
 *
 * 为什么需要它：
 *   前端「订单管理 → 新建/编辑订单」里要能替会员挑地址、也能现场新增一个地址
 *   （views/trade/orderBase/components/DeliveryAddressEdit.vue 调
 *     POST /manage/account/userDeliveryAddress/add，
 *     OrderBaseEdit.vue 调 GET /manage/account/userDeliveryAddress/list）。
 *   但后端此前**只有 Front\Auth 那一套**（front/account/userDeliveryAddress/*，走 checkLoginUserId），
 *   管理端路由一条都没有 → 前台建单时地址下拉永远是空的、新增必定 404。
 *
 * 与 Front 版的差异（这是关键，别直接照抄 Front）：
 *   - Front 版 user_id 一律取自登录态（checkLoginUserId），因为那是"会员改自己的地址"；
 *   - 管理端是"管理员替指定会员操作"，user_id 必须由请求带入，做参数校验但不能覆盖成管理员自己。
 *   - 因此这里不做 checkDataRights（那是防会员横向越权的），但地址归属仍强制要求 user_id 存在。
 *
 * @package Modules\Account\Http\Controllers\Manage
 */
class DeliveryAddressController extends BaseController
{

    private $userDeliveryAddressService;
    private $userDeliveryAddressValidator;

    public function __construct(
        UserDeliveryAddressService   $userDeliveryAddressService,
        UserDeliveryAddressValidator $userDeliveryAddressValidator
    )
    {
        $this->userDeliveryAddressService = $userDeliveryAddressService;
        $this->userDeliveryAddressValidator = $userDeliveryAddressValidator;
    }


    /**
     * 会员收货地址列表（按 user_id 过滤）
     */
    public function list(Request $request)
    {
        $data = $this->userDeliveryAddressService->list($request, new UserDeliveryAddressCriteria($request));

        return Respond::success($data);
    }


    /**
     * 新增会员收货地址
     */
    public function add(Request $request)
    {
        $this->userDeliveryAddressValidator->with($request->all())->passesOrFail('create');

        $user_id = (int)$request->input('user_id', 0);
        if (!$user_id) {
            return Respond::error(__('请先选择所属会员'));
        }

        $data = $this->userDeliveryAddressService->saveAddress($request);

        return Respond::success($data);
    }


    /**
     * 修改会员收货地址
     */
    public function edit(Request $request)
    {
        $ud_id = (int)$request->input('ud_id', 0);
        if (!$ud_id) {
            return Respond::error(__('地址编号不能为空'));
        }

        $row = $this->userDeliveryAddressService->get($ud_id);
        if (empty($row)) {
            return Respond::error(__('地址不存在'));
        }

        $this->userDeliveryAddressValidator->with($request->all())->passesOrFail('update');

        // user_id 沿用该地址原本的归属，避免前端漏传时把地址改挂到 0 号用户下
        $request['user_id'] = $row['user_id'];
        $data = $this->userDeliveryAddressService->saveAddress($request, $ud_id);

        return Respond::success($data);
    }


    /**
     * 删除会员收货地址
     */
    public function remove(Request $request)
    {
        $ud_id = (int)$request->input('ud_id', 0);
        if (!$ud_id) {
            return Respond::error(__('地址编号不能为空'));
        }

        $data = $this->userDeliveryAddressService->remove($ud_id);

        return Respond::success($data);
    }

}

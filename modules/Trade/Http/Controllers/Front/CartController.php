<?php

namespace Modules\Trade\Http\Controllers\Front;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Models\User;
use Modules\Trade\Services\UserCartService;
use Modules\Account\Services\UserDeliveryAddressService;

class CartController extends BaseController
{
    private $userCartService;
    private $userDeliveryAddressService;
    private $userId;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserCartService $userCartService, UserDeliveryAddressService $userDeliveryAddressService)
    {
        $this->userCartService = $userCartService;
        $this->userDeliveryAddressService = $userDeliveryAddressService;

        $this->userId = User::getUserId();
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $request = $request->all();
        $request['user_id'] = $this->userId;
        $data = $this->userCartService->getCartList($request);

        return Respond::success($data);
    }


    /**
     * 添加购物车
     */
    public function add(Request $request)
    {
        $user_cart = $request->all();
        $user_cart['user_id'] = $this->userId;

        // 调用用户购物车服务添加购物车
        $flag = $this->userCartService->addCart($user_cart);
        if ($flag) {
            return Respond::success($user_cart);
        } else {
            return Respond::error();
        }
    }


    /**
     * 修改购物车数量
     */
    public function editQuantity(Request $request)
    {
        $cart_id = $request->input('cart_id', 0);
        $cart_quantity = $request->input('cart_quantity', 1);

        try {
            $result = $this->userCartService->editQuantity($cart_id, $this->userId, $cart_quantity);
            return Respond::success($result);
        } catch (\Exception $e) {
            return Respond::error($e->getMessage());
        }
    }


    /**
     * 修改购物车选中状态
     */
    public function sel(Request $request)
    {
        $input = $request->all();
        $input['cart_select'] = $request->boolean('cart_select');

        try {
            $result = $this->userCartService->selCart($input, $this->userId);
            return Respond::success($result);
        } catch (\Exception $e) {
            return Respond::error($e->getMessage());
        }
    }


    /**
     * 购物车结算页面
     */
    public function checkout(Request $request)
    {
        $ud_id = $request->input('ud_id', 0);
        //todo 获取用户的收货地址
        $user_delivery_address = $this->userDeliveryAddressService->getOneAddress($ud_id, $this->userId);
        $request['user_delivery_address'] = $user_delivery_address;
        $data = $this->userCartService->checkout($request, $this->userId);

        return Respond::success($data);
    }


    /**
     * 删除购物车
     */
    public function remove(Request $request)
    {
        $data = $this->userCartService->remove($request->get('cart_id'));

        return Respond::success($data);
    }


    /**
     * 批量删除购物车
     */
    public function removeBatch(Request $request)
    {
        $cart_id = $request->get('cart_id', '');
        $cart_ids = explode(',', $cart_id);
        if (!empty($cart_ids)) {
            $this->userCartService->remove($cart_ids);
        }

        return Respond::success($cart_ids);
    }

}

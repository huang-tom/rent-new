<?php

namespace Modules\Trade\Http\Controllers\Front;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Models\User;
use Modules\Trade\Repositories\Criteria\OrderReturnCriteria;
use Modules\Trade\Services\OrderReturnService;

class ReturnController extends BaseController
{
    private $orderReturnService;
    private $userId;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(OrderReturnService $orderReturnService)
    {
        $this->orderReturnService = $orderReturnService;

        $this->userId = User::getUserId();
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $request['buyer_user_id'] = $this->userId;
        $data = $this->orderReturnService->list($request, new OrderReturnCriteria($request));

        return Respond::success($data);
    }


    /**
     * 获取退单商品信息
     */
    public function returnItem(Request $request)
    {
        $order_id = $request->input('order_id', 0);
        $order_item_id = $request->input('order_item_id', 0);
        $data = $this->orderReturnService->returnItem($order_id, $order_item_id, $this->userId);

        return Respond::success($data);
    }


    /**
     * 添加退款
     */
    public function add(Request $request)
    {
        $req = $request->all();
        $data = $this->orderReturnService->addReturn($this->userId, $req);

        return Respond::success($data);
    }


    /**
     * 退单详情
     */
    public function get(Request $request)
    {
        $return_id = $request->input('return_id', '');
        $data = $this->orderReturnService->getReturnDetail($return_id);
        if ($data['buyer_user_id'] != $this->userId) {
            throw new ErrorException(__('无操作权限!'));
        }

        return Respond::success($data);
    }


    /**
     * 取消退单
     */
    public function cancel(Request $request)
    {
        $return_id = $request->input('return_id', '');
        $return_row = $this->orderReturnService->get($return_id);
        if ($return_row['buyer_user_id'] != $this->userId) {
            throw new ErrorException(__('无操作权限!'));
        }

        $res = $this->orderReturnService->cancel($return_id, $return_row);

        return Respond::success([$res]);
    }


    /**
     * 填写退单物流单号
     */
    public function edit(Request $request)
    {
        $return_id = $request->input('return_id', '');
        $return_row = $this->orderReturnService->get($return_id);
        if ($return_row['buyer_user_id'] != $this->userId) {
            throw new ErrorException(__('无操作权限!'));
        }

        $res = $this->orderReturnService->editReturnExpress($request, $return_id);

        return Respond::success([$res]);
    }


}

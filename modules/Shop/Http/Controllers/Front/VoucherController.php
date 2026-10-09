<?php

namespace Modules\Shop\Http\Controllers\Front;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Models\User;
use Modules\Shop\Repositories\Criteria\UserVoucherCriteria;
use Modules\Shop\Services\UserVoucherService;

class VoucherController extends BaseController
{
    private $userVoucherService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserVoucherService $userVoucherService)
    {
        $this->userVoucherService = $userVoucherService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $user_id = checkLoginUserId();
        $request['user_id'] = $user_id;
        $data = $this->userVoucherService->list($request, new UserVoucherCriteria($request));

        return Respond::success($data);
    }

    /**
     * 列表
     */
    public function getEachVoucherNum(Request $request)
    {
        $user_id = checkLoginUserId();
        $data = $this->userVoucherService->getEachVoucherNum($user_id);

        return Respond::success($data);
    }


    /**
     * 领取优惠券
     */
    public function add(Request $request)
    {
        $activity_id = $request->get('activity_id', 0);
        $user_id = User::getUserId();
        $data = $this->userVoucherService->addVoucher($activity_id, $user_id);

        return Respond::success($data);
    }

}

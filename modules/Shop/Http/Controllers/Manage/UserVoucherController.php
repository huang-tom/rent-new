<?php

namespace Modules\Shop\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Shop\Http\Controllers\ShopController;
use Modules\Shop\Repositories\Criteria\UserVoucherCriteria;
use Modules\Shop\Services\UserVoucherService;

class UserVoucherController extends ShopController
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
        $data = $this->userVoucherService->list($request, new UserVoucherCriteria($request));

        return Respond::success($data);
    }

}

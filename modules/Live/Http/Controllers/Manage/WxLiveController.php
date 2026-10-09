<?php

namespace Modules\Live\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Live\Services\WxLiveService;

class WxLiveController extends BaseController
{
    private $wxLiveService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(WxLiveService $wxLiveService)
    {
        $this->wxLiveService = $wxLiveService;
    }


    /**
    * 商品列表
    */
    public function getApproved(Request $request)
    {
        $data = $this->wxLiveService->getApproved($request);

        return Respond::success($data);
    }

}

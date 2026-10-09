<?php

namespace Modules\Live\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Live\Services\WxLiveService;

class LiveController extends BaseController
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
     * 列表
     */
    public function list(Request $request)
    {
        $data = [];

        return Respond::success($data);
    }

}

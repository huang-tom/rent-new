<?php

namespace Modules\Pay\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Services\UserInfoService;
use Modules\Pay\Repositories\Criteria\ConsumeRecordCriteria;
use Modules\Pay\Services\ConsumeRecordService;

class ConsumeRecordController extends BaseController
{
    private $consumeRecordService;
    private $userInfoService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ConsumeRecordService $consumeRecordService, UserInfoService $userInfoService)
    {
        $this->consumeRecordService = $consumeRecordService;
        $this->userInfoService = $userInfoService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->consumeRecordService->list($request, new ConsumeRecordCriteria($request));
        if (!empty($data['data'])) {
            $data['data'] = $this->userInfoService->fixUserInfo($data['data']);
        }

        return Respond::success($data);
    }


}

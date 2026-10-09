<?php

namespace Modules\Pay\Http\Controllers\Front;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Account\Repositories\Models\User;
use Modules\Pay\Repositories\Criteria\UserExpHistoryCriteria;
use Modules\Pay\Services\UserExpHistoryService;
use Modules\Pay\Services\UserResourceService;

class ResourceController extends BaseController
{
    private $userResourceService;
    private $userExpHistoryService;
    private $userId;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserResourceService $userResourceService, UserExpHistoryService $userExpHistoryService)
    {
        $this->userResourceService = $userResourceService;
        $this->userExpHistoryService = $userExpHistoryService;
        $this->userId = User::getUserId();
    }


    /**
     * 获取签到状态
     */
    public function signState()
    {
        if ($this->userResourceService->getSignState($this->userId)) {
            return Respond::success([], __('已经签到'), 0, 200);
        } else {
            return Respond::error(__('尚未签到'), 0, 250);
        }
    }


    /**
     * 签到操作
     */
    public function signIn()
    {
        $data = $this->userResourceService->sign($this->userId);

        return Respond::success($data, __('签到成功'));
    }


    /**
     * 获取签到数据
     */
    public function getSignInfo()
    {
        $data = $this->userResourceService->getSignInfo($this->userId);

        return Respond::success($data);
    }


    /**
     * 经验值列表
     */
    public function listsExp(Request $request)
    {
        $request['user_id'] = $this->userId;
        $data = $this->userExpHistoryService->list($request, new UserExpHistoryCriteria($request));

        return Respond::success($data);
    }

}

<?php

namespace Modules\Shop\Http\Controllers\Front;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Models\User;
use Modules\Shop\Services\UserSearchHistoryService;

class MobileController extends BaseController
{
    private $userSearchHistoryService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserSearchHistoryService $userSearchHistoryService)
    {
        $this->userSearchHistoryService = $userSearchHistoryService;
    }


    /**
     * 获取搜索关键词
     */
    public function getSearchInfo(Request $request)
    {
        $user_id = User::getUserId();
        $data = $this->userSearchHistoryService->getSearchInfo($user_id);

        return Respond::success($data);
    }

}

<?php

namespace Modules\Account\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Criteria\UserLevelCriteria;
use Modules\Account\Repositories\Validators\UserLevelValidator;
use Modules\Account\Services\UserLevelService;

class UserLevelController extends BaseController
{
    private $userLevelService;
    private $userLevelValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserLevelService $userLevelService, UserLevelValidator $userLevelValidator)
    {
        $this->userLevelService = $userLevelService;
        $this->userLevelValidator = $userLevelValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {

        $data = $this->userLevelService->list($request, new UserLevelCriteria($request));

        return Respond::success($data);
    }

    /**
     * 格式化请求数组
     * @param $request
     * @return array
     */
    public function formatRequest($request)
    {
        $data = [
            'user_level_name' => $request['user_level_name'], //等级名称
            'user_level_exp' => $request->input('user_level_exp', 0),
            'user_level_spend' => $request->input('user_level_spend', 0), //累计消费
            'user_level_logo' => $request->input('user_level_logo', ''), //图标
            'user_level_rate' => $request->input('user_level_rate', 0) //折扣率
        ];

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->userLevelValidator->with($request->all())->passesOrFail('create');
        $data = $this->userLevelService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $user_level_id = $request->get('user_level_id', -1);
        $this->userLevelValidator->setId($request['user_level_id']);
        $this->userLevelValidator->with($request->all())->passesOrFail('update');
        $data = $this->userLevelService->edit($user_level_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $user_level_id = $request->get('user_level_id', -1);
        $data = $this->userLevelService->remove($user_level_id);

        return Respond::success($data);
    }

}

<?php

namespace Modules\Account\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Criteria\UserTagBaseCriteria;
use Modules\Account\Repositories\Validators\UserTagBaseValidator;
use Modules\Account\Services\UserTagBaseService;

class UserTagBaseController extends BaseController
{

    private $userTagBaseService;
    private $userTagBaseValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserTagBaseService $userTagBaseService, UserTagBaseValidator $userTagBaseValidator)
    {
        $this->userTagBaseService = $userTagBaseService;
        $this->userTagBaseValidator = $userTagBaseValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->userTagBaseService->list($request, new UserTagBaseCriteria($request));

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
            'tag_title' => $request['tag_title'], //名称
            'tag_group_id' => $request['tag_group_id'], //分组ID
            'tag_sort' => $request->input('tag_sort', 0), //排序
            'tag_enable' => $request->boolean('tag_enable') //是否启用
        ];

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->userTagBaseValidator->with($request->all())->passesOrFail('create');
        $data = $this->userTagBaseService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $tag_id = $request->get('tag_id', -1);
        $this->userTagBaseValidator->setId($request['tag_id']);
        $this->userTagBaseValidator->with($request->all())->passesOrFail('update');
        $data = $this->userTagBaseService->edit($tag_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $tag_id = $request->get('tag_id');
        $state_data = [];

        if ($request->has('tag_enable')) {
            $state_data['tag_enable'] = $request->boolean('tag_enable');
        }

        // 更新状态
        if ($tag_id && !empty($state_data)) {
            $result = $this->userTagBaseService->edit($tag_id, $state_data);
        } else {
            throw new ErrorException(__('数据有误'));
        }

        return Respond::success($result);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $data = $this->userTagBaseService->remove($request['tag_id']);

        return Respond::success($data);
    }


}

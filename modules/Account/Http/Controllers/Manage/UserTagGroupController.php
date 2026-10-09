<?php

namespace Modules\Account\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Criteria\UserTagGroupCriteria;
use Modules\Account\Repositories\Validators\UserTagGroupValidator;
use Modules\Account\Services\UserTagGroupService;

class UserTagGroupController extends BaseController
{

    private $userTagGroupService;
    private $userTagGroupValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserTagGroupService $userTagGroupService, UserTagGroupValidator $userTagGroupValidator)
    {
        $this->userTagGroupService = $userTagGroupService;
        $this->userTagGroupValidator = $userTagGroupValidator;
    }


    /**
     * tree
     */
    public function tree(Request $request)
    {
        $data = $this->userTagGroupService->tree($request);

        return Respond::success($data);
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->userTagGroupService->list($request, new UserTagGroupCriteria($request));

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->userTagGroupValidator->with($request->all())->passesOrFail('create');
        $data = $this->userTagGroupService->add([
            'tag_group_name' => $request['tag_group_name'], //名称
            'tag_group_sort' => $request->input('tag_group_sort', 0), //排序
            'tag_group_enable' => $request->boolean('tag_group_enable'), //是否启用
        ]);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $tag_group_id = $request->get('tag_group_id');
        $this->userTagGroupValidator->setId($tag_group_id);
        $this->userTagGroupValidator->with($request->all())->passesOrFail('update');
        $data = $this->userTagGroupService->edit($tag_group_id, [
            'tag_group_name' => $request['tag_group_name'], //名称
            'tag_group_sort' => $request->input('tag_group_sort', 0), //排序
            'tag_group_enable' => $request->boolean('tag_group_enable'), //是否启用
        ]);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $tag_group_id = $request->get('tag_group_id');
        $data = $this->userTagGroupService->remove($tag_group_id);

        return Respond::success($data);
    }
}

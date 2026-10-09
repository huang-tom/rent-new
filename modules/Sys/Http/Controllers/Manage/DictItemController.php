<?php

namespace Modules\Sys\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Repositories\Criteria\DictItemCriteria;
use Modules\Sys\Repositories\Validators\DictItemValidator;
use Modules\Sys\Services\DictItemService;

class DictItemController extends BaseController
{
    private $dictItemService;
    private $dictItemValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(DictItemService $dictItemService, DictItemValidator $dictItemValidator)
    {
        $this->dictItemService = $dictItemService;
        $this->dictItemValidator = $dictItemValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->dictItemService->list($request, new DictItemCriteria($request));

        return Respond::success($data);
    }


    /**
     * 格式化请求数据
     * @param $request
     * @return array
     */
    public function formatRequest($request)
    {

        $data = [
            'dict_item_id' => $request['dict_item_id'],     //主键编号
            'dict_id' => $request['dict_id'],          //字典类型
            'dict_item_name' => $request['dict_item_name'],   //字典项名称
            'dict_item_code' => $request['dict_item_code'],   //字典项值
            'dict_item_status' => $request->input('dict_item_status', 0),    //是否使用(BOOL):0-未用;1-使用
            'dict_item_sort' => $request->input('dict_item_sort', 0),    //显示顺序:从小到大
            'dict_item_note' => $request->input('dict_item_note', ''),   //备注
            'dict_item_enable' => $request->boolean('dict_item_enable', 0) //是否启用
        ];

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->dictItemValidator->with($request->all())->passesOrFail('create');
        $data = $this->dictItemService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $dict_item_id = $request['dict_item_id'];
        $this->dictItemValidator->setId($dict_item_id);
        $this->dictItemValidator->with($request->all())->passesOrFail('update');
        $data = $this->dictItemService->edit($dict_item_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $dict_item_id = $request['dict_item_id'];
        $data = $this->dictItemService->remove($dict_item_id);

        return Respond::success($data);
    }

}

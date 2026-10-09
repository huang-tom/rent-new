<?php

namespace Modules\Sys\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Repositories\Criteria\DictBaseCriteria;
use Modules\Sys\Repositories\Validators\DictBaseValidator;
use Modules\Sys\Services\DictBaseService;

class DictBaseController extends BaseController
{
    private $dictBaseService;
    private $dictBaseValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(DictBaseService $dictBaseService, DictBaseValidator $dictBaseValidator)
    {
        $this->dictBaseService = $dictBaseService;
        $this->dictBaseValidator = $dictBaseValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->dictBaseService->list($request, new DictBaseCriteria($request));

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
            'dict_id' => $request['dict_id'],   //主键编号
            'dict_name' => $request->input('dict_name', ''),    //字典名称
            'dict_sort' => $request->input('dict_sort', 0),    //显示顺序:从小到大
            'dict_note' => $request->input('dict_note', ''),   //字典备注
            'dict_enable' => $request->boolean('dict_enable', 0) //是否启用
        ];

        return $data;
    }

    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->dictBaseValidator->with($request->all())->passesOrFail('create');
        $data = $this->dictBaseService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $dict_id = $request['dict_id'];
        $this->dictBaseValidator->setId($dict_id);
        $this->dictBaseValidator->with($request->all())->passesOrFail('update');
        $data = $this->dictBaseService->edit($dict_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $dict_id = $request['dict_id'];
        $data = $this->dictBaseService->remove($dict_id);

        return Respond::success($data);
    }
}

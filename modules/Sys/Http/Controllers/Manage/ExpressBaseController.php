<?php

namespace Modules\Sys\Http\Controllers\Manage;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Sys\Repositories\Criteria\ExpressBaseCriteria;
use Modules\Sys\Repositories\Validators\ExpressBaseValidator;
use Modules\Sys\Services\ExpressBaseService;

class ExpressBaseController extends BaseController
{

    private $expressBaseService;
    private $expressBaseValidator;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ExpressBaseService $expressBaseService, ExpressBaseValidator $expressBaseValidator)
    {
        $this->expressBaseService = $expressBaseService;
        $this->expressBaseValidator = $expressBaseValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->expressBaseService->list($request, new ExpressBaseCriteria($request));

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
            'express_name' => $request['express_name'],   //快递名称
            'express_pinyin' => $request['express_pinyin'], //快递编码
            'express_pinyin_100' => $request->input('express_pinyin_100', ''),   //快递公司100编码
            'express_site' => $request->input('express_site', ''),   //快递官网
            'express_order' => $request->input('express_order', 0), //排序
            'express_enable' => $request->boolean('express_enable', 0), //是否启用
        ];

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->validateRequest($request, 'create');
        $formatted_request = $this->formatRequest($request);
        $data = $this->expressBaseService->add($formatted_request);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $express_id = $request['express_id'];
        $this->validateRequest($request, 'update', $express_id);
        $formatted_request = $this->formatRequest($request);
        $data = $this->expressBaseService->edit($express_id, $formatted_request);

        return Respond::success($data);
    }


    /**
     * 验证请求
     */
    private function validateRequest(Request $request, string $action, $id = null)
    {
        if ($id) {
            $this->expressBaseValidator->setId($id);
        }
        $this->expressBaseValidator->with($request->all())->passesOrFail($action);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $data = $this->expressBaseService->removeExpress($request);

        return Respond::success($data);
    }


    /**
     * 批量删除
     * [新增 2026-09-22] 补上前端一直在调用、后端却缺失的路由（原为 404）
     */
    public function removeBatch(Request $request)
    {
        $data = $this->expressBaseService->removeExpressBatch($request);

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $express_id = $request->input('express_id', 0);
        $data = $this->expressBaseService->edit($express_id, [
            'express_enable' => $request->boolean('express_enable', false)
        ]);

        return Respond::success($data);
    }


    public function enableList(Request $request)
    {
        $request['express_enable'] = true;
        $data = $this->expressBaseService->list($request, new ExpressBaseCriteria($request));

        return Respond::success($data);
    }

}

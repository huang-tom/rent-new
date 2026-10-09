<?php

namespace Modules\Pt\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Pt\Services\ProductAskBaseService;

/**
 * Class ProductAskBaseController.
 *
 * 商品咨询（问答）后台接口。
 *
 * [新增 2026-09-23] 原为整块缺失（路由 404）：
 *   POST /manage/pt/productAskBase/list|add|edit|remove|removeBatch
 *
 * @package Modules\Pt\Http\Controllers\Manage
 */
class ProductAskBaseController extends BaseController
{
    private $productAskBaseService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ProductAskBaseService $productAskBaseService)
    {
        $this->productAskBaseService = $productAskBaseService;
    }


    /**
     * 咨询列表
     */
    public function list(Request $request)
    {
        $data = $this->productAskBaseService->list($request);

        return Respond::success($data);
    }


    /**
     * 新增咨询
     */
    public function add(Request $request)
    {
        $data = $this->productAskBaseService->addAsk($request);

        return Respond::success($data);
    }


    /**
     * 编辑咨询（含列表「是否展示」开关的局部更新）
     */
    public function edit(Request $request)
    {
        $data = $this->productAskBaseService->editAsk($request);

        return Respond::success($data);
    }


    /**
     * 删除单条咨询
     */
    public function remove(Request $request)
    {
        $data = $this->productAskBaseService->removeAsk($request);

        return Respond::success($data);
    }


    /**
     * 批量删除咨询
     *
     * 与 remove 共用同一套入参归一化逻辑（标量 / 逗号串 / 数组都吃），
     * 单独留一个路由是因为前端 api/pt/productAskBase.ts 两个函数打了不同 URL。
     */
    public function removeBatch(Request $request)
    {
        $data = $this->productAskBaseService->removeAsk($request);

        return Respond::success($data);
    }
}

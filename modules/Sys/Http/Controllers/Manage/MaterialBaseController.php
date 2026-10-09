<?php

namespace Modules\Sys\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Repositories\Criteria\MaterialBaseCriteria;
use Modules\Sys\Services\MaterialBaseService;

class MaterialBaseController extends BaseController
{
    private $materialBaseService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(MaterialBaseService $materialBaseService)
    {
        $this->materialBaseService = $materialBaseService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->materialBaseService->list($request, new MaterialBaseCriteria($request));

        return Respond::success($data);
    }


    /**
     * 格式化请求数据
     * @param $request
     * @return array
     */
    public function formatRequest($request)
    {
        $material_type = (string)$request->input('material_type', 'image');
        if (!in_array($material_type, ['video', 'other', 'image', 'audio', 'document'], true)) {
            $material_type = 'image';
        }

        $data = [
            'store_id' => 0,
            'gallery_id' => (int)$request->input('gallery_id', 0),
            // [修正 2026-09-22] 原为 $request->input('feedback_type_genus', 'image')，
            // 键名是从「反馈类型」模块复制过来的，素材表单里永远不存在这个键 ——
            // 于是「编辑素材」每次都把 material_type 强行写回 image。
            // 对一个视频素材改名，它就会变成图片素材（material_type 是 enum，值不会报错，
            // 只会静默改变语义：前台按 image 渲染视频素材）。现改为读 material_type。
            'material_type' => $material_type,
            'material_name' => (string)$request->input('material_name', ''),
            'material_desc' => (string)$request->input('material_desc', ''),
            'material_alt' => (string)$request->input('material_alt', ''),
            'material_url' => (string)$request->input('material_url', ''),
            'material_path' => (string)$request->input('material_path', ''),
            'material_size' => (int)$request->input('material_size', 0),
            'material_mime_type' => (string)$request->input('material_mime_type', 'image/png')
        ];

        return $data;
    }

    /**
     * 新增
     * [新增 2026-09-22] 前端 api/sys/material.ts 的 doAdd() 原先指向未注册的路由
     */
    public function add(Request $request)
    {
        $data = $this->materialBaseService->addMaterial($request);

        return Respond::success($data);
    }


    /**
     * 批量删除
     * [新增 2026-09-22] 前端「素材管理」的「批量删除」按钮一直在调，原先 404
     */
    public function removeBatch(Request $request)
    {
        $data = $this->materialBaseService->removeBatchMaterial($request);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $material_id = $request['material_id'];
        $data = $this->materialBaseService->edit($material_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $data = $this->materialBaseService->remove($request['material_id']);

        return Respond::success($data);
    }

}

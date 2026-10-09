<?php

namespace Modules\Sys\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Repositories\Criteria\MaterialGalleryCriteria;
use Modules\Sys\Services\MaterialGalleryService;

class MaterialGalleryController extends BaseController
{
    private $materialGalleryService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(MaterialGalleryService $materialGalleryService)
    {
        $this->materialGalleryService = $materialGalleryService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->materialGalleryService->list($request, new MaterialGalleryCriteria($request));

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {

        $attributes['gallery_name'] = $request->get('gallery_name');  //名称
        if ($request->get('gallery_sort')) {
            $attributes['gallery_sort'] = $request['gallery_sort'];  //排序
        }

        $data = $this->materialGalleryService->add($attributes);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $gallery_id = $request->get('gallery_id', 0);
        $data = $this->materialGalleryService->edit($gallery_id, $request->all());

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        //todo 获取ID集合
        $gallery_id = $request->get('gallery_id');
        if (!$gallery_id) throw new ErrorException('请选择要删除的行');

        $data = $this->materialGalleryService->remove($gallery_id);

        return Respond::success($data);
    }

}

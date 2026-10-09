<?php

namespace Modules\Sys\Http\Controllers\Manage;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Sys\Services\DistrictBaseService;

class DistrictBaseController extends BaseController
{
    private $districtBaseService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(DistrictBaseService $districtBaseService)
    {
        $this->districtBaseService = $districtBaseService;
    }


    /**
     * 列表
     */
    public function tree(Request $request)
    {
        $data = $this->districtBaseService->tree($request);

        return Respond::success($data);
    }


    /**
     * 扁平列表（带分页）
     * [新增 2026-09-22] 补上一直声明、却从未实现的控制器方法（原为静默 404）
     * 服务层方法名是 flatList()，不能叫 list()（会与混淆父类 BaseService::list 签名冲突）
     */
    public function list(Request $request)
    {
        $data = $this->districtBaseService->flatList($request);

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $data = $this->districtBaseService->add([
            'district_name' => $request['district_name'],   //地区名称
            'district_parent_id' => $request->input('district_parent_id', 0), //上级编号
            'district_sort' => $request->input('district_sort', 0) //排序
        ]);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $district_id = $request['district_id'];
        $data = $this->districtBaseService->edit($district_id, [
            'district_name' => $request['district_name'],   //地区名称
            'district_parent_id' => $request->input('district_parent_id', 0), //上级编号
            'district_sort' => $request->input('district_sort', 0) //排序
        ]);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $data = $this->districtBaseService->remove($request['district_id']);

        return Respond::success($data);
    }


    /**
     * 批量删除
     * [新增 2026-09-22] 补上前端一直在调用、后端却缺失的路由（原为 404）
     */
    public function removeBatch(Request $request)
    {
        $data = $this->districtBaseService->removeBatch($request);

        return Respond::success($data);
    }

}

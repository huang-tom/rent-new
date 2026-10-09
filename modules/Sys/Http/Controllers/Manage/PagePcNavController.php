<?php

namespace Modules\Sys\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Repositories\Criteria\PagePcNavCriteria;
use Modules\Sys\Repositories\Validators\PagePcNavValidator;
use Modules\Sys\Services\PagePcNavService;

class PagePcNavController extends BaseController
{
    private $pagePcNavService;
    private $pagePcNavValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PagePcNavService $pagePcNavService, PagePcNavValidator $pagePcNavValidator)
    {
        $this->pagePcNavService = $pagePcNavService;
        $this->pagePcNavValidator = $pagePcNavValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->pagePcNavService->list($request, new PagePcNavCriteria($request));

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
            'nav_title' => $request['nav_title'],
            'nav_url' => $request->input('nav_url', ''),
            'nav_position' => $request['nav_position'],
            'nav_target_blank' => $request->boolean('nav_target_blank'),
            'nav_image' => $request->input('nav_image', ''),
            'nav_dropdown_menu' => $request->input('nav_dropdown_menu', ''),
            'nav_order' => $request->input('nav_order', 0),
            'nav_enable' => $request->boolean('nav_enable'),
            'nav_buildin' => $request->input('nav_buildin', 0)
        ];

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->pagePcNavValidator->with($request->all())->passesOrFail('create');
        $data = $this->pagePcNavService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $nav_id = $request['nav_id'];

        $this->pagePcNavValidator->setId($nav_id);
        $this->pagePcNavValidator->with($request->all())->passesOrFail('update');
        $data = $this->pagePcNavService->edit($nav_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $data = $this->pagePcNavService->remove($request['nav_id']);

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $nav_id = $request['nav_id'];
        $state_data = [];
        $state_data['nav_enable'] = $request->boolean('nav_enable', 0);

        // 更新状态
        if ($nav_id && !empty($state_data)) {
            $this->pagePcNavService->edit($nav_id, $state_data);
        } else {
            throw new ErrorException(__('数据有误'));
        }

        return Respond::success($state_data);
    }

}

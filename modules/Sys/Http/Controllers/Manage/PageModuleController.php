<?php

namespace Modules\Sys\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Repositories\Criteria\PageModuleCriteria;
use Modules\Sys\Repositories\Validators\PageModuleValidator;
use Modules\Sys\Services\ConfigBaseService;
use Modules\Sys\Services\PageModuleService;

class PageModuleController extends BaseController
{
    private $pageModuleService;
    private $pageModuleValidator;
    private $configBaseService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        PageModuleService   $pageModuleService,
        PageModuleValidator $pageModuleValidator,
        ConfigBaseService   $configBaseService
    )
    {
        $this->pageModuleService = $pageModuleService;
        $this->pageModuleValidator = $pageModuleValidator;
        $this->configBaseService = $configBaseService;
    }


    /**
     * 获取商城楼层模板库
     */
    public function listTpl()
    {
        $data = $this->configBaseService->getServiceData(['module_type' => 2]);

        return Respond::success($data);
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->pageModuleService->list($request, new PageModuleCriteria($request));

        return Respond::success($data);
    }


    /**
     * 添加
     */
    public function add(Request $request)
    {
        $this->pageModuleValidator->with($request->all())->passesOrFail('create');

        $add_row = [
            'pm_enable' => 0,
            'page_id' => $request->get('page_id'),
            'module_id' => $request->get('module_id')
        ];

        $pm_json = $request->get('pm_json');

        // [本地化] 装修模板 JSON 内的资源/跳转地址原指向官方测试域名，
        // 保存时统一替换为本站 URL_PC。此处仅为字符串替换，不产生任何外呼。
        // 相比原实现（只匹配单个厂商测试域名），改为按域名后缀匹配，避免漏替换。
        if (!empty($pm_json)) {
            $pm_json = preg_replace(
                '#(https?:)?//(?:[\w-]+\.)*(?:shopsuite\.cn|suteshop\.com)#i',
                rtrim(env('URL_PC'), '/'),
                $pm_json
            );
            $add_row['pm_json'] = json_decode($pm_json);
        }

        $result = $this->pageModuleService->add($add_row);
        $pm_id = $result->getKey();
        $data = $this->pageModuleService->get($pm_id);
        $data['pm_json'] = $pm_json;

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $pm_id = $request['pm_id'];
        $this->pageModuleValidator->setId($pm_id);
        $this->pageModuleValidator->with($request->all())->passesOrFail('update');
        $pm_json = json_decode($request->get('pm_json'));
        $edit_row = [
            'pm_json' => $pm_json
        ];
        $data = $this->pageModuleService->edit($pm_id, $edit_row);

        return Respond::success($data);
    }


    /**
     * 修改启用状态
     */
    public function enable(Request $request)
    {
        $pm_id = $request['pm_id'];
        $pm_enable = $request->get('usable') == 'usable' ? 1 : 0;
        $data = $this->pageModuleService->edit($pm_id, ['pm_enable' => $pm_enable]);

        return Respond::success($data);
    }


    /**
     * 排序
     */
    public function sort(Request $request)
    {
        $data = $this->pageModuleService->sort($request);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $data = $this->pageModuleService->remove($request['pm_id']);

        return Respond::success($data);
    }

}

<?php

namespace Modules\Sys\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Repositories\Criteria\ConfigTypeCriteria;
use Modules\Sys\Repositories\Validators\ConfigTypeValidator;
use Modules\Sys\Services\ConfigTypeService;

class ConfigTypeController extends BaseController
{
    private $configTypeService;
    private $configTypeValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ConfigTypeService $configTypeService, ConfigTypeValidator $configTypeValidator)
    {
        $this->configTypeService = $configTypeService;
        $this->configTypeValidator = $configTypeValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->configTypeService->list($request, new ConfigTypeCriteria($request));

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->configTypeValidator->with($request->all())->passesOrFail('create');
        $data = $this->configTypeService->add([
            'config_type_name' => $request->get('config_type_name'),
            'config_type_module' => $request->get('config_type_module'),
            'config_type_sort' => $request->get('config_type_sort', 0)
        ]);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $config_type_id = $request->get('config_type_id', -1);
        $this->configTypeValidator->setId($config_type_id);
        $this->configTypeValidator->with($request->all())->passesOrFail('update');
        $data = $this->configTypeService->edit($config_type_id, [
            'config_type_name' => $request->get('config_type_name'),
            'config_type_module' => $request->get('config_type_module'),
            'config_type_sort' => $request->get('config_type_sort', 0)
        ]);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $config_type_id = $request->get('config_type_id', -1);
        $data = $this->configTypeService->removeType($config_type_id);

        return Respond::success($data);
    }
}

<?php

namespace Modules\Sys\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Repositories\Criteria\ContractTypeCriteria;
use Modules\Sys\Repositories\Validators\ContractTypeValidator;
use Modules\Sys\Services\ContractTypeService;

class ContractTypeController extends BaseController
{
    private $contractTypeService;
    private $contractTypeValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ContractTypeService $contractTypeService, ContractTypeValidator $contractTypeValidator)
    {
        $this->contractTypeService = $contractTypeService;
        $this->contractTypeValidator = $contractTypeValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->contractTypeService->list($request, new ContractTypeCriteria($request));

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
            'contract_type_name' => $request->input('contract_type_name'),   //保障名称
            'contract_type_desc' => $request->input('contract_type_desc', ''), //保障简写
            'contract_type_text' => $request->input('contract_type_text', ''), //保障描述
            'contract_type_deposit' => $request->input('contract_type_deposit', 0), //保证金
            'contract_type_icon' => $request->input('contract_type_icon', ''), //图标
            'contract_type_url' => $request->input('contract_type_url', ''), //说明网址
            'contract_type_order' => $request->input('contract_type_order', 0), //保障排序
            'contract_type_enable' => $request->boolean('contract_type_enable', 0), //是否开启(BOOL):0-关闭;1-开启
            'contract_type_buildin' => $request->boolean('contract_type_buildin', 0), //系统内置(BOOL): 0-非内置;1-系统内置
        ];

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->contractTypeValidator->with($request->all())->passesOrFail('create');
        $data = $this->contractTypeService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $contract_type_id = $request['contract_type_id'];
        $this->contractTypeValidator->setId($contract_type_id);
        $this->contractTypeValidator->with($request->all())->passesOrFail('update');
        $data = $this->contractTypeService->edit($contract_type_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $data = $this->contractTypeService->remove($request['contract_type_id']);

        return Respond::success($data);
    }


    public function editState(Request $request)
    {
        $contract_type_id = $request->get('contract_type_id');
        $state_data = [];

        if ($request->has('contract_type_enable')) {
            $state_data['contract_type_enable'] = $request->boolean('contract_type_enable');
        }

        if ($request->has('contract_type_buildin')) {
            $state_data['contract_type_buildin'] = $request->boolean('contract_type_buildin');
        }

        // 更新状态
        if ($contract_type_id && !empty($state_data)) {
            $this->contractTypeService->edit($contract_type_id, $state_data);
        } else {
            throw new ErrorException(__('数据有误'));
        }

        return Respond::success($state_data);
    }

}

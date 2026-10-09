<?php

namespace Modules\Pay\Http\Controllers\Manage;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Pay\Repositories\Criteria\BaseBankCriteria;
use Modules\Pay\Services\BaseBankService;

class BaseBankController extends BaseController
{
    private $baseBankService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(BaseBankService $baseBankService)
    {
        $this->baseBankService = $baseBankService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->baseBankService->list($request, new BaseBankCriteria($request));

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
            'bank_name' => $request->input('bank_name', ''),   //银行名称
            'bank_remark' => $request->input('bank_remark', ''), //备注
            'bank_order' => $request->input('bank_order', 0),   //排序
            'bank_enable' => $request->boolean('bank_enable'),   //是否启用
            'settlement_account_type_id' => $request->input('settlement_account_type_id', 1004), //账户类别(ENUM):1001-微信;1002-支付宝;1003-现金;1004-银行
        ];

        return $data;
    }

    /**
     * 新增
     */
    public function add(Request $request)
    {
        $data = $this->baseBankService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $bank_id = $request->input('bank_id', -1);
        $data = $this->baseBankService->edit($bank_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $bank_id = $request->input('bank_id', -1);
        $data = $this->baseBankService->remove($bank_id);

        return Respond::success($data);
    }

}

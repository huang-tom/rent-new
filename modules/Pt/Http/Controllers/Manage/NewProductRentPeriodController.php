<?php

namespace Modules\Pt\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Pt\Repositories\Criteria\NewProductRentPeriodCriteria;
use Modules\Pt\Repositories\Validators\NewProductRentPeriodValidator;
use Modules\Pt\Services\NewProductRentPeriodService;

class NewProductRentPeriodController extends BaseController
{
    private $newProductRentPeriodService;
    private $newProductRentPeriodValidator;

    public function __construct(
        NewProductRentPeriodService   $newProductRentPeriodService,
        NewProductRentPeriodValidator $newProductRentPeriodValidator
    )
    {
        $this->newProductRentPeriodService = $newProductRentPeriodService;
        $this->newProductRentPeriodValidator = $newProductRentPeriodValidator;
    }

    /**
     * 列表（商品页勾选租期也走此接口）
     */
    public function list(Request $request)
    {
        $data = $this->newProductRentPeriodService->list($request, new NewProductRentPeriodCriteria($request));

        return Respond::success($data);
    }

    /**
     * 格式化请求
     */
    public function formatRequest(Request $request)
    {
        return [
            'period_name' => $request->input('period_name', ''),
            'period_days' => (int)$request->input('period_days', 0),
            'period_enable' => (int)$request->input('period_enable', 1),
            'period_order' => (int)$request->input('period_order', 50),
        ];
    }

    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->newProductRentPeriodValidator->with($request->all())->passesOrFail('create');
        $data = $this->newProductRentPeriodService->addPeriod($this->formatRequest($request));

        return Respond::success($data);
    }

    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $period_id = $request->get('period_id', -1);
        $this->newProductRentPeriodValidator->setId($period_id);
        $this->newProductRentPeriodValidator->with($request->all())->passesOrFail('update');
        $data = $this->newProductRentPeriodService->editPeriod($period_id, $this->formatRequest($request));

        return Respond::success($data);
    }

    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $period_id = $request->get('period_id', -1);
        $data = $this->newProductRentPeriodService->removePeriod($period_id);

        return Respond::success($data);
    }

    /**
     * 启停
     */
    public function editState(Request $request)
    {
        $period_id = $request->get('period_id', -1);
        $period_enable = (int)$request->input('period_enable', 1);
        $data = $this->newProductRentPeriodService->editState($period_id, $period_enable);

        return Respond::success($data);
    }
}

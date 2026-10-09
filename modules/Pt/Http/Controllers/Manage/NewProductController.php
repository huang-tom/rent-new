<?php

namespace Modules\Pt\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Pt\Repositories\Criteria\NewProductCriteria;
use Modules\Pt\Repositories\Validators\NewProductValidator;
use Modules\Pt\Services\NewProductService;

class NewProductController extends BaseController
{
    private $newProductService;
    private $newProductValidator;

    public function __construct(NewProductService $newProductService, NewProductValidator $newProductValidator)
    {
        $this->newProductService = $newProductService;
        $this->newProductValidator = $newProductValidator;
    }

    /**
     * 列表
     * 筛选：keyword / category_id / sale_mode / status(1上架 0下架 2待审核)
     */
    public function list(Request $request)
    {
        $data = $this->newProductService->list($request, new NewProductCriteria($request));

        return Respond::success($data);
    }

    /**
     * 顶部统计卡片
     */
    public function statistics(Request $request)
    {
        $data = $this->newProductService->getStatistics();

        return Respond::success($data);
    }

    /**
     * 详情（编辑回显）
     */
    public function get(Request $request)
    {
        $product_id = $request->get('product_id', 0);
        $data = $this->newProductService->getProduct($product_id);

        return Respond::success($data);
    }

    /**
     * 新增/修改
     */
    public function save(Request $request)
    {
        $this->newProductValidator->with($request->all())->passesOrFail('create');
        $data = $this->newProductService->saveProduct($request);

        return Respond::success($data);
    }

    /**
     * 上下架
     */
    public function editState(Request $request)
    {
        $product_id = (int)$request->get('product_id', 0);
        $product_state = (int)$request->input('product_state', 0);
        $data = $this->newProductService->editState($product_id, $product_state);

        return Respond::success($data);
    }

    /**
     * 审核通过
     */
    public function audit(Request $request)
    {
        $product_id = (int)$request->get('product_id', 0);
        $data = $this->newProductService->audit($product_id);

        return Respond::success($data);
    }

    /**
     * 批量上下架
     */
    public function batchEditState(Request $request)
    {
        $product_ids = $request->input('product_ids', []);
        if (is_string($product_ids)) {
            $product_ids = array_filter(explode(',', $product_ids));
        }
        $product_state = (int)$request->input('product_state', 0);
        $affected_rows = $this->newProductService->batchEditState($product_ids, $product_state);
        $msg = '成功更新了' . $affected_rows . '个商品';

        return Respond::success($affected_rows, $msg);
    }

    /**
     * 批量审核
     */
    public function batchAudit(Request $request)
    {
        $product_ids = $request->input('product_ids', []);
        if (is_string($product_ids)) {
            $product_ids = array_filter(explode(',', $product_ids));
        }
        $affected_rows = $this->newProductService->batchAudit($product_ids);
        $msg = '成功审核了' . $affected_rows . '个商品';

        return Respond::success($affected_rows, $msg);
    }

    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $product_id = $request->get('product_id', -1);
        $data = $this->newProductService->removeProduct($product_id);

        return Respond::success($data);
    }
}

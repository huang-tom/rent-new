<?php

namespace Modules\Pt\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use App\Support\SimpleXlsx;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Invoicing\Repositories\Criteria\StockBillItemCriteria;
use Modules\Invoicing\Services\StockBillItemService;
use Modules\Pt\Repositories\Criteria\ProductItemCriteria;
use Modules\Pt\Services\ProductItemService;

class ProductItemController extends BaseController
{
    private $productItemService;
    private $stockBillItemService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ProductItemService $productItemService, StockBillItemService $stockBillItemService)
    {
        $this->productItemService = $productItemService;
        $this->stockBillItemService = $stockBillItemService;
    }


    /**
     * 下载「导入商品规格(SKU)」模板（xlsx）
     *
     * [新增 2026-09-23] 补 GET /manage/pt/productItem/exportTemp（原为 404）。
     *
     * 可达性证据：`views/pt/productBase/index.vue:14` 的活按钮
     *   `<el-button v-permissions="{ permission: ['/manage/pt/productBase/edit'] }"
     *     type="warning" @click="importEditItem">`，打开的是 ProductItemImport 弹窗，
     *   弹窗里的「下载模板」调这个接口。权限已下发，所以是真缺陷。
     *   （注意前端把入口放在「商品列表」页，而不是 sku 页 —— 所以按页面文件名找会漏。）
     *
     * ⚠️ 同 productBase：配套的 `productItem/importTemp` 后端**完全不存在**，
     *    模板只是列契约。列顺序与 pt_product_item 的字段对应：
     *   商品ID(必填) / SKU名称 / SKU货号 / 条形码 / 成本价 / 售价 / 市场价 /
     *   积分价 / 库存 / 库存预警值 / 重量 / 体积 / 规格值编号(逗号分隔) / 是否启用(1001启用,1002禁用)
     */
    public function exportTemp(Request $request)
    {
        $xlsx = new SimpleXlsx(__('导入商品规格'));

        $xlsx->addHeader([
            '商品ID', 'SKU名称', 'SKU货号', '条形码', '成本价', '售价', '市场价',
            '积分价', '库存', '库存预警值', '重量', '体积', '规格值编号', '是否启用',
        ]);

        $xlsx->addRow([
            '请填商品ID', '示例规格', 'SKU-DEMO-001', '6900000000000', '50.00', '99.00', '129.00',
            '0.00', 100, 5, '0.500000', '0.00', '', 1001,
        ]);

        return $xlsx->download(__('导入商品规格模板') . '.xlsx');
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->productItemService->list($request, new ProductItemCriteria($request));

        return Respond::success($data);
    }


    /**
     * editState
     */
    public function editState(Request $request)
    {
        $item_id = $request->get('item_id');
        $state_data = [];

        if ($request->has('item_enable')) {
            $state_data['item_enable'] = $request['item_enable'];
        }

        // 更新状态
        if ($item_id && !empty($state_data)) {
            $result = $this->productItemService->edit($item_id, $state_data);
            if ($result) {
                return Respond::success($state_data);
            } else {
                throw new ErrorException(__('更新失败'));
            }
        }
    }

    public function getStockBillItems(Request $request)
    {
        $data = $this->stockBillItemService->list($request, new StockBillItemCriteria($request));

        return Respond::success($data);
    }

    public function editStock(Request $request)
    {
        $item_id = $request->get('item_id', 0);
        $inputs[$item_id] = $request;
        $data = $this->productItemService->batchEditStock($inputs);

        return Respond::success($data);
    }

    public function getStockWarningItems(Request $request)
    {
        $data = $this->productItemService->getStockWarningItems($request);

        return Respond::success($data);
    }

}

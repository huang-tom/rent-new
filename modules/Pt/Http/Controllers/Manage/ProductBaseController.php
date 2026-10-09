<?php

namespace Modules\Pt\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use App\Support\SimpleXlsx;
use App\Support\StateCode;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Pt\Repositories\Criteria\ProductIndexCriteria;
use Modules\Pt\Repositories\Validators\ProductBaseValidator;
use Modules\Pt\Services\ProductBaseService;
use Modules\Pt\Services\ProductIndexService;

class ProductBaseController extends BaseController
{
    private $productBaseService;
    private $productIndexService;
    private $productBaseValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        ProductBaseService   $productBaseService,
        ProductIndexService  $productIndexService,
        ProductBaseValidator $productBaseValidator)
    {
        $this->productBaseService = $productBaseService;
        $this->productIndexService = $productIndexService;
        $this->productBaseValidator = $productBaseValidator;
    }


    /**
     * 下载「导入单规格商品」模板（xlsx）
     *
     * [新增 2026-09-23] 补 GET /manage/pt/productBase/exportTemp（原为 404）。
     *
     * 可达性证据（为什么这条必须补）：
     *   `views/pt/productBase/index.vue:11` 有一个**活按钮**
     *   `<el-button v-permissions="{ permission: ['/manage/pt/productBase/edit'] }"
     *     type="success" @click="importEdit">` —— 该权限已随包下发给 4 个角色，
     *   点开的就是 ProductBaseImport 弹窗，里面的「下载模板」直接调这个接口。
     *   （第一版排查时本地 grep 静默返回空，把这个组件误判成 orphan，已用 reachability_audit.py 纠正。）
     *
     * ⚠️ 配套的**导入**接口 `productBase/importTemp` 在本项目里**完全不存在**
     *    （Controller/Service/路由全无，vendor 只下发了弹窗没下发实现）。
     *    所以这里只保证「模板能下载、列名是唯一的字段契约」。
     *    真正启用导入前，必须按下面的列顺序实现 importTemp —— 那属于**新增功能**，
     *    涉及商品多表写入（base/index/info/...）与字段校验，不是补一条断链能覆盖的。
     *
     * 列顺序即契约（与 ProductBaseService::saveProduct 读取的字段一一对应）：
     *   商品名称(必填) / SPU货号 / 商品分类ID(必填) / 品牌ID / 商品卖点 /
     *   每人限购 / 售卖区域ID / 商品主图URL /
     *   SKU货号 / 售价(必填) / 成本价 / 市场价 / 库存(必填) / 库存预警值
     */
    public function exportTemp(Request $request)
    {
        $xlsx = new SimpleXlsx(__('导入单规格商品'));

        $xlsx->addHeader([
            '商品名称', 'SPU货号', '商品分类ID', '品牌ID', '商品卖点',
            '每人限购', '售卖区域ID', '商品主图URL',
            'SKU货号', '售价', '成本价', '市场价', '库存', '库存预警值',
        ]);

        // 示例行：刻意用「示例」字样的分类ID占位，避免照抄后被当成真实分类
        $xlsx->addRow([
            '示例商品-请勿直接导入', 'SPU-DEMO-001', '请填分类ID', 0, '示例卖点文字',
            0, 0, '',
            'SKU-DEMO-001', '99.00', '50.00', '129.00', 100, 5,
        ]);

        return $xlsx->download(__('导入单规格商品模板') . '.xlsx');
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->productIndexService->list($request, new ProductIndexCriteria($request));

        return Respond::success($data);
    }


    /**
     * 新增/修改商品信息
     */
    public function save(Request $request)
    {
        $this->productBaseValidator->with($request->all())->passesOrFail('create');
        $data = $this->productBaseService->saveProduct($request);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $data = $this->productBaseService->removeProduct($request->get('product_id'));

        return Respond::success($data);
    }


    /**
     * editState
     */
    public function editState(Request $request)
    {
        $data = $this->productBaseService->editState($request);

        return Respond::success($data);
    }


    /**
     *  更新产品佣金比例
     */
    public function editCommissionRate(Request $request)
    {
        $product_id = $request->get('product_id', -1);

        $edit_data = [];
        if ($request->has('product_commission_rate')) {
            $edit_data['product_commission_rate'] = $request['product_commission_rate'];
        }

        // 更新数据
        if ($product_id && !empty($edit_data)) {
            $result = $this->productBaseService->edit($product_id, $edit_data);
            if ($result) {
                return true;
            } else {
                throw new ErrorException(__('更新失败'));
            }
        }

        return Respond::success($edit_data);
    }

    /**
     * 更新商品排序值
     */
    public function editSort(Request $request)
    {
        $product_id = $request->get('product_id', -1);
        $edit_data = [];

        if ($request->has('product_order')) {
            $edit_data['product_order'] = $request['product_order'];
        }

        // 更新数据
        if ($product_id && !empty($edit_data)) {
            $result = $this->productBaseService->edit($product_id, $edit_data);
            if ($result) {
                return true;
            } else {
                throw new ErrorException(__('更新失败'));
            }
        }

        return Respond::success($edit_data);
    }


    /**
     * 获取商品信息
     */
    public function getProduct(Request $request)
    {
        $product_id = $request->get('product_id');
        $data = $this->productBaseService->getProduct($product_id);

        return Respond::success($data);
    }


    /**
     * 获取商品SKU列表
     */
    public function listItem(Request $request)
    {
        $request['product_state_id'] = StateCode::PRODUCT_STATE_NORMAL;
        $request['product_verify_id'] = StateCode::PRODUCT_VERIFY_PASSED;
        $item_id = $request->input('item_id', '');
        unset($request['item_id']);

        $item_ids = explode(',', $item_id);
        $request->merge(['item_ids' => $item_ids]);

        $data = $this->productIndexService->listItem($request);

        return Respond::success($data);
    }


    public function batchEditState(Request $request)
    {
        $product_ids_str = $request->input('product_ids', '');
        $product_ids = explode(',', $product_ids_str);
        $product_state_id = $request->input('product_state_id', 0);
        $affected_rows = $this->productBaseService->batchEditState($product_ids, $product_state_id);
        $msg = __('成功更新了') . $affected_rows . __('个商品');

        return Respond::success($affected_rows, $msg);
    }

}

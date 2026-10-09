<?php

namespace Modules\Pt\Services;

use App\Support\StateCode;
use Kuteshop\Core\Service\BaseService;
use Modules\Invoicing\Repositories\Contracts\StockBillItemRepository;
use Modules\Pt\Repositories\Contracts\ProductBaseRepository;
use Modules\Pt\Repositories\Contracts\ProductIndexRepository;
use Modules\Pt\Repositories\Contracts\ProductItemRepository;

use Illuminate\Support\Facades\DB;
use App\Exceptions\ErrorException;
use Modules\Pt\Repositories\Criteria\ProductItemCriteria;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;


/**
 * Class ProductItemService.
 *
 * @package Modules\Pt\Services
 */
class ProductItemService extends BaseService
{
    private $stockBillItemRepository;
    private $configBaseRepository;
    private $productBaseRepository;
    private $productIndexRepository;


    public function __construct(
        ProductItemRepository   $productItemRepository,
        StockBillItemRepository $stockBillItemRepository,
        ConfigBaseRepository    $configBaseRepository,
        ProductBaseRepository   $productBaseRepository,
        ProductIndexRepository  $productIndexRepository
    )
    {
        $this->repository = $productItemRepository;
        $this->stockBillItemRepository = $stockBillItemRepository;
        $this->configBaseRepository = $configBaseRepository;
        $this->productBaseRepository = $productBaseRepository;
        $this->productIndexRepository = $productIndexRepository;
    }


    /**
     * 获取列表
     * @return array
     */
    public function list($request, $criteria)
    {
        $limit = $request->get('size') ?? 10;

        if ($product_name = $request->input('product_name', '')) {
            $product_ids = $this->productIndexRepository->findKey([['product_name', 'LIKE', '%' . $product_name . '%']]);
            $request['product_ids'] = $product_ids;
        }

        $data = $this->repository->list($criteria, $limit);

        return $data;
    }


    /**
     * 更改库存
     * @param $inputs
     * @return true
     * @throws ErrorException
     */
    public function batchEditStock($inputs)
    {
        $item_ids = array_column($inputs, 'item_id');

        // 将 inputs 按 item_id 转换为关联数组
        $edit_stock_input_map = [];
        foreach ($inputs as $input) {
            $edit_stock_input_map[$input['item_id']] = $input;
        }

        // 获取商品 SKU 信息
        $product_items = $this->repository->gets($item_ids);
        if (empty($product_items)) {
            throw new ErrorException(__('商品 SKU 信息不存在'));
        }

        $stock_bill_items = [];

        // 开始数据库事务
        DB::beginTransaction();
        try {
            foreach ($product_items as $item_id => $product_item) {
                $input = $edit_stock_input_map[$product_item['item_id']] ?? null;

                if ($input) {
                    $stock_bill_item = [];
                    $stock_bill_item['product_id'] = $product_item['product_id'];
                    $stock_bill_item['item_id'] = $product_item['item_id'];
                    $stock_bill_item['item_name'] = $product_item['item_name'];
                    $stock_bill_item['bill_item_quantity'] = $input['item_quantity'];
                    $stock_bill_item['warehouse_item_quantity'] = $product_item['item_quantity'];

                    // 判断出入库类型
                    if ($input['bill_type_id'] == StateCode::BILL_TYPE_IN) {
                        $stock_bill_item['bill_type_id'] = StateCode::BILL_TYPE_IN;
                        $stock_bill_item['stock_transport_type_id'] = StateCode::STOCK_IN_OTHER;

                        // 增加库存
                        $product_items[$item_id]['item_quantity'] += $input['item_quantity'];
                    } else {
                        $stock_bill_item['bill_type_id'] = StateCode::BILL_TYPE_OUT;
                        $stock_bill_item['stock_transport_type_id'] = StateCode::STOCK_OUT_OTHER;

                        // 检查是否有足够的库存
                        if ($product_item['available_quantity'] >= $input['item_quantity']) {
                            // 减少库存
                            $product_items[$item_id]['item_quantity'] -= $input['item_quantity'];
                        } else {
                            throw new ErrorException(__('出库数量不能大于总库存！'));
                        }
                    }

                    $stock_bill_item['bill_item_unit_price'] = $product_item['item_unit_price'];
                    $stock_bill_item['bill_item_subtotal'] = $product_item['item_unit_price'] * $stock_bill_item['bill_item_quantity'];

                    $stock_bill_items[] = $stock_bill_item;
                }
            }

            if (!empty($stock_bill_items)) {
                // 保存库存单据
                if (!$this->stockBillItemRepository->addBatch($stock_bill_items)) {
                    throw new ErrorException(__('保存出入库单据失败！'));
                }

                if (!$this->repository->batchUpdateQuantity($product_items)) {
                    throw new ErrorException(__('修改商品 SKU 信息失败！'));
                }

                // 提交事务
                DB::commit();
            }

            return true;
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();
            throw new ErrorException($e->getMessage());
        }
    }


    public function getStockWarningItems($request)
    {
        // 从配置服务中获取库存警告阈值，默认为 5
        $stockWarning = $this->configBaseRepository->getConfig('stock_warning', 5);
        $request['stock_warning'] = $stockWarning;

        $data = $this->list($request, new ProductItemCriteria($request));
        if (!empty($data['data'])) {
            $product_ids = array_column_unique($data['data'], 'product_id');
            $product_base_rows = $this->productBaseRepository->gets($product_ids);

            foreach ($data['data'] as $k => $row) {
                $product_id = $row['product_id'];
                if (isset($product_base_rows[$product_id])) {
                    $data['data'][$k]['product_name'] = $product_base_rows[$product_id]['product_name'];
                    $data['data'][$k]['product_image'] = $product_base_rows[$product_id]['product_image'];
                }
            }
        }

        return $data;
    }


}

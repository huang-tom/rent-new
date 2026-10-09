<?php

namespace Modules\Pt\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Pt\Repositories\Contracts\ProductCategoryRepository;
use Modules\Pt\Repositories\Contracts\ProductInfoRepository;
use Modules\Pt\Repositories\Contracts\ProductSpecItemRepository;
use Modules\Pt\Repositories\Contracts\ProductSpecRepository;
use App\Exceptions\ErrorException;
use Modules\Pt\Repositories\Contracts\ProductTypeRepository;

/**
 * Class ProductSpecService.
 *
 * @package Modules\Pt\Services
 */
class ProductSpecService extends BaseService
{

    private $productSpecItemRepository;
    private $productCategoryRepository;
    private $productTypeRepository;
    private $productInfoRepository;

    public function __construct(
        ProductSpecRepository     $productSpecRepository,
        ProductSpecItemRepository $productSpecItemRepository,
        ProductCategoryRepository $productCategoryRepository,
        ProductTypeRepository     $productTypeRepository,
        ProductInfoRepository     $productInfoRepository
    )
    {
        $this->repository = $productSpecRepository;
        $this->productSpecItemRepository = $productSpecItemRepository;
        $this->productCategoryRepository = $productCategoryRepository;
        $this->productTypeRepository = $productTypeRepository;
        $this->productInfoRepository = $productInfoRepository;
    }


    /**
     * 规格树形数据
     * @param $request
     * @return array
     */
    public function tree($request)
    {
        $spec_lists = $this->repository->find([]);
        $category_ids = array_column($spec_lists, 'category_id');
        $categories = $this->productCategoryRepository->gets($category_ids);

        $brandList = [];
        foreach ($categories as $category) {
            $brands = [];
            foreach ($spec_lists as $brand) {
                if ($brand['category_id'] == $category['category_id']) {
                    $brands[] = $brand;
                }
            }

            if (count($brands) > 0) {
                $brandList[] = [
                    'children' => empty($brands) ? null : $brands,
                    'spec_id' => $category['category_id'],
                    'spec_name' => $category['category_name']
                ];
            }
        }

        return $brandList;
    }


    /**
     * 删除
     * @param $spec_id
     * @return bool
     * @throws ErrorException
     */
    public function remove($spec_id)
    {
        $spec_row = $this->repository->getOne($spec_id);
        if (empty($spec_row)) {
            throw new ErrorException(__('规格不存在'));
        }

        if ($spec_row['spec_buildin']) {
            throw new ErrorException(__("系统内置，不可删除"));
        }

        $count = $this->productSpecItemRepository->getNum(['spec_id' => $spec_id]);
        if ($count > 0) {
            throw new ErrorException(sprintf(__('有 %d 个规格选项，不可删除'), $count));
        }

        $tmp_rows = $this->productTypeRepository->find([['spec_ids', 'FIND_IN_SET', [$spec_id]]]);
        $count = count($tmp_rows);
        if ($count > 0) {
            throw new ErrorException(sprintf(__("有 %d 条类型使用，不可删除"), $count));
        }

        $product_spec_rows = $this->productInfoRepository->find([['spec_ids', 'FIND_IN_SET', [$spec_id]]]);
        $count = count($product_spec_rows);
        if ($count > 0) {
            throw new ErrorException(sprintf(__("已被 %d 个SPU商品使用，不可删除"), $count));
        }

        $result = $this->repository->remove($spec_id);

        if ($result) {
            return true;
        } else {
            throw new ErrorException(__('删除失败'));
        }
    }


    /**
     * 获取规格选项
     * @param $spec_ids
     * @return array|mixed
     */
    public function getSpecItems($spec_ids = [-1])
    {
        $spec_list = $this->repository->gets($spec_ids);
        if ($spec_list) {
            $spec_item_list = $this->productSpecItemRepository->find([
                ['spec_id', 'IN', $spec_ids],
                ['spec_item_enable', '=', 1]
            ]);
            $spec_items = [];
            foreach ($spec_item_list as $spec_item) {
                if (!array_key_exists($spec_item['spec_id'], $spec_items)) {
                    $spec_items[$spec_item['spec_id']] = [];
                }
                $spec_items[$spec_item['spec_id']][] = $spec_item;
            }

            foreach ($spec_list as $spec_id => $assist_row) {
                if (isset($spec_items[$assist_row['spec_id']])) {
                    $spec_list[$spec_id]['items'] = $spec_items[$assist_row['spec_id']];
                }
            }
        }

        return $spec_list;
    }

}

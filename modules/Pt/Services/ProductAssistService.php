<?php

namespace Modules\Pt\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Pt\Repositories\Contracts\ProductAssistItemRepository;
use Modules\Pt\Repositories\Contracts\ProductAssistRepository;
use App\Exceptions\ErrorException;
use Modules\Pt\Repositories\Contracts\ProductCategoryRepository;

/**
 * Class ProductAssistService.
 *
 * @package Modules\Pt\Services
 */
class ProductAssistService extends BaseService
{
    private $productAssistItemRepository;
    private $productCategoryRepository;

    public function __construct(
        ProductAssistRepository     $productAssistRepository,
        ProductAssistItemRepository $productAssistItemRepository,
        ProductCategoryRepository   $productCategoryRepository
    )
    {
        $this->repository = $productAssistRepository;
        $this->productAssistItemRepository = $productAssistItemRepository;
        $this->productCategoryRepository = $productCategoryRepository;
    }


    /**
     * 删除
     * @param $assist_id
     * @return bool
     * @throws ErrorException
     */
    public function remove($assist_id)
    {
        $count = $this->productAssistItemRepository->getNum([['assist_id', '=', $assist_id]]);
        if ($count > 0) {
            throw new ErrorException(sprintf(__('有 %d 个属性选项，不可删除！'), $count));
        }

        $result = $this->repository->remove($assist_id);

        if ($result) {
            return true;
        } else {
            throw new ErrorException(__('删除失败'));
        }
    }


    /**
     * 获取属性选项
     * @param $assist_ids
     * @return array|mixed
     */
    public function getAssistItems($assist_ids = [-1])
    {
        $assist_list = $this->repository->gets($assist_ids);
        if ($assist_list) {
            $assist_item_list = $this->productAssistItemRepository->find([['assist_id', 'IN', $assist_ids]]);
            $assist_items = [];
            foreach ($assist_item_list as $assist_item) {
                if (!array_key_exists($assist_item['assist_id'], $assist_items)) {
                    $assist_items[$assist_item['assist_id']] = [];
                }
                $assist_items[$assist_item['assist_id']][] = $assist_item;
            }

            foreach ($assist_list as $assist_id => $assist_row) {
                if (isset($assist_items[$assist_row['assist_id']])) {
                    $assist_list[$assist_id]['items'] = $assist_items[$assist_row['assist_id']];
                }
            }
        }

        return $assist_list;
    }


    /**
     * 获取属性树形数据
     *
     * @return array
     */
    public function getTree()
    {

        $rows = [];

        $assists_rows = $this->repository->find([], ['assist_sort' => 'ASC', 'assist_id' => 'ASC']);
        $category_ids = array_column_unique($assists_rows, 'category_id');
        $category_rows = $this->productCategoryRepository->gets($category_ids);

        // 遍历分类，按分类将属性分组
        foreach ($category_rows as $category_row) {
            $assists = [];

            // 遍历属性数据，并按 category_id 匹配分类
            foreach ($assists_rows as $assists_row) {
                if ($assists_row['category_id'] == $category_row['category_id']) {
                    $assists[] = $assists_row;
                }
            }

            // 如果有对应的属性，则构建树形数据
            if (!empty($assists)) {
                $rows[] = [
                    'assist_id' => $category_row['category_id'],
                    'assist_name' => $category_row['category_name'],
                    'children' => $assists
                ];
            }
        }

        return $rows;
    }


}

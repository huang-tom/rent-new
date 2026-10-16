<?php

namespace Modules\Pt\Services;

use App\Exceptions\ErrorException;
use Kuteshop\Core\Service\BaseService;
use Modules\Pt\Repositories\Contracts\NewProductPromoCateRepository;
use Modules\Pt\Repositories\Contracts\NewProductPromoRepository;
use Modules\Pt\Repositories\Contracts\NewProductRepository;

/**
 * Class NewProductPromoService.
 *
 * @package Modules\Pt\Services
 */
class NewProductPromoService extends BaseService
{
    private $newProductPromoCateRepository;
    private $newProductRepository;

    public function __construct(
        NewProductPromoRepository     $newProductPromoRepository,
        NewProductPromoCateRepository $newProductPromoCateRepository,
        NewProductRepository          $newProductRepository
    )
    {
        $this->repository = $newProductPromoRepository;
        $this->newProductPromoCateRepository = $newProductPromoCateRepository;
        $this->newProductRepository = $newProductRepository;
    }

    /**
     * 按 type(推广分类ID) 读取推广商品
     * 仅返回：product_id / product_number / product_image
     * 排序：sort ASC, id ASC
     */
    public function listByType($type)
    {
        $type = (int)$type;
        if ($type <= 0) {
            throw new ErrorException('推广分类不能为空');
        }

        $this->assertCateExists($type);

        $rows = $this->repository->find(
            [
                ['type', '=', $type],
                ['is_deleted', '=', 0],
            ],
            [
                'sort' => 'ASC',
                'id' => 'ASC',
            ]
        ) ?: [];

        if (!$rows) {
            return [];
        }

        $product_ids = array_values(array_unique(array_column($rows, 'product_id')));
        // 只查未删除商品，已删商品不返回
        $product_rows = $this->newProductRepository->find([
            ['product_id', 'IN', $product_ids],
            ['is_deleted', '=', 0],
        ]) ?: [];
        $products = [];
        foreach ($product_rows as $product) {
            $products[$product['product_id']] = $product;
        }

        $list = [];
        foreach ($rows as $row) {
            $product = $products[$row['product_id']] ?? null;
            if (!$product) {
                continue;
            }
            $list[] = [
                'id' => $row['id'],
                'product_id' => (int)$row['product_id'],
                'product_number' => $product['product_number'] ?? '',
                'product_image' => $product['product_image'] ?? '',
                'sort' => (int)$row['sort'],
            ];
        }

        return $list;
    }

    /**
     * 绑定推广商品
     * @param int $type cate_id
     * @param array $product_ids
     * @param int $sort 默认排序起点
     */
    public function addProducts($type, array $product_ids, $sort = 50)
    {
        $type = (int)$type;
        $this->assertCateExists($type);

        $product_ids = array_values(array_unique(array_filter(array_map('intval', $product_ids))));
        if (!$product_ids) {
            throw new ErrorException('请选择商品');
        }

        $now = getDateTime();
        $success = 0;
        $new_category_ids = [];
        foreach ($product_ids as $product_id) {
            $product = $this->newProductRepository->getOne($product_id);
            if (!$product || !empty($product['is_deleted'])) {
                continue;
            }

            // 已存在且未删则跳过；已软删则恢复
            $exist = $this->repository->find([
                'type' => $type,
                'product_id' => $product_id,
            ]) ?: [];
            $exist_row = $exist ? reset($exist) : null;

            if ($exist_row && empty($exist_row['is_deleted'])) {
                continue;
            }

            if ($exist_row && !empty($exist_row['is_deleted'])) {
                $this->repository->edit($exist_row['id'], [
                    'is_deleted' => 0,
                    'sort' => (int)$sort,
                    'update_time' => $now,
                ]);
                $this->collectCategoryId($product, $new_category_ids);
                $success++;
                continue;
            }

            $this->repository->add([
                'type' => $type,
                'product_id' => $product_id,
                'sort' => (int)$sort,
                'is_deleted' => 0,
                'add_time' => $now,
                'update_time' => $now,
            ]);
            $this->collectCategoryId($product, $new_category_ids);
            $success++;
        }

        if ($success <= 0) {
            throw new ErrorException('没有可绑定的商品（可能已绑定或商品不存在）');
        }

        if ($new_category_ids) {
            $cate = $this->newProductPromoCateRepository->getOne($type);
            $category_ids = $this->parseCategoryIds($cate['category_id'] ?? '');
            foreach ($new_category_ids as $category_id) {
                if (!in_array($category_id, $category_ids, true)) {
                    $category_ids[] = $category_id;
                }
            }
            $this->newProductPromoCateRepository->edit($type, [
                'category_id' => implode(',', $category_ids),
                'update_time' => $now,
            ]);
        }

        return $success;
    }

    /**
     * 软删除绑定
     */
    public function removePromo($id)
    {
        $row = $this->repository->getOne($id);
        if (!$row || !empty($row['is_deleted'])) {
            throw new ErrorException('推广商品不存在');
        }

        $result = $this->repository->edit($id, [
            'is_deleted' => 1,
            'update_time' => getDateTime(),
        ]);
        if (!$result) {
            throw new ErrorException('删除失败');
        }

        $this->refreshCateCategoryId((int)$row['type']);

        return true;
    }

    /**
     * 修改排序
     */
    public function editSort($id, $sort)
    {
        $row = $this->repository->getOne($id);
        if (!$row || !empty($row['is_deleted'])) {
            throw new ErrorException('推广商品不存在');
        }

        return $this->repository->edit($id, [
            'sort' => (int)$sort,
            'update_time' => getDateTime(),
        ]);
    }

    private function assertCateExists($cate_id)
    {
        $cate = $this->newProductPromoCateRepository->getOne($cate_id);
        if (!$cate || !empty($cate['is_deleted'])) {
            throw new ErrorException('推广分类不存在');
        }
    }

    /**
     * 解绑后按剩余绑定商品重写推广分类 category_id（逗号分隔，未维护分类的不写入）
     */
    private function refreshCateCategoryId($cate_id)
    {
        $cate = $this->newProductPromoCateRepository->getOne($cate_id);
        if (!$cate || !empty($cate['is_deleted'])) {
            return;
        }

        $rows = $this->repository->find(
            [
                ['type', '=', $cate_id],
                ['is_deleted', '=', 0],
            ],
            [
                'sort' => 'ASC',
                'id' => 'ASC',
            ]
        ) ?: [];

        $category_ids = [];
        if ($rows) {
            $product_ids = array_values(array_unique(array_column($rows, 'product_id')));
            $product_rows = $this->newProductRepository->find([
                ['product_id', 'IN', $product_ids],
                ['is_deleted', '=', 0],
            ]) ?: [];
            $products = [];
            foreach ($product_rows as $product) {
                $products[$product['product_id']] = $product;
            }
            foreach ($rows as $row) {
                $product = $products[$row['product_id']] ?? null;
                if (!$product) {
                    continue;
                }
                $this->collectCategoryId($product, $category_ids);
            }
        }

        $next = implode(',', $category_ids);
        $current = implode(',', $this->parseCategoryIds($cate['category_id'] ?? ''));
        if ($next === $current) {
            return;
        }

        $this->newProductPromoCateRepository->edit($cate_id, [
            'category_id' => $next,
            'update_time' => getDateTime(),
        ]);
    }

    private function collectCategoryId(array $product, array &$category_ids)
    {
        $category_id = (int)($product['category_id'] ?? 0);
        if ($category_id > 0 && !in_array($category_id, $category_ids, true)) {
            $category_ids[] = $category_id;
        }
    }

    /**
     * 逗号分隔的商品分类ID，0 和空值丢弃
     */
    private function parseCategoryIds($value)
    {
        $category_ids = [];
        foreach (explode(',', (string)$value) as $part) {
            $category_id = (int)trim($part);
            if ($category_id > 0 && !in_array($category_id, $category_ids, true)) {
                $category_ids[] = $category_id;
            }
        }

        return $category_ids;
    }
}

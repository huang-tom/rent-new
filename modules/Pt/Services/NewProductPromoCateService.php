<?php

namespace Modules\Pt\Services;

use App\Exceptions\ErrorException;
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Pt\Repositories\Contracts\NewProductPromoCateRepository;
use Modules\Pt\Repositories\Contracts\NewProductPromoRepository;
use Modules\Pt\Repositories\Contracts\ProductCategoryRepository;

/**
 * Class NewProductPromoCateService.
 *
 * @package Modules\Pt\Services
 */
class NewProductPromoCateService extends BaseService
{
    /** 默认菜单 */
    const CATE_TYPE_DEFAULT = 1;
    /** 分类菜单 */
    const CATE_TYPE_CATEGORY = 2;

    private $newProductPromoRepository;
    private $productCategoryRepository;

    public function __construct(
        NewProductPromoCateRepository $newProductPromoCateRepository,
        NewProductPromoRepository     $newProductPromoRepository,
        ProductCategoryRepository     $productCategoryRepository
    )
    {
        $this->repository = $newProductPromoCateRepository;
        $this->newProductPromoRepository = $newProductPromoRepository;
        $this->productCategoryRepository = $productCategoryRepository;
    }

    /**
     * 列表（附带关联分类名）
     */
    public function list($request, $criteria)
    {
        $data = parent::list($request, $criteria);
        if (empty($data['data'])) {
            return $data;
        }

        $category_ids = array_unique(array_filter(array_column($data['data'], 'category_id')));
        $category_map = [];
        if ($category_ids) {
            $categories = $this->productCategoryRepository->gets($category_ids) ?: [];
            foreach ($categories as $category) {
                $category_map[$category['category_id']] = $category['category_name'] ?? '';
            }
        }

        $cate_ids = array_column($data['data'], 'cate_id');
        $count_map = [];
        if ($cate_ids) {
            $count_rows = DB::table('pt_new_product_promo')
                ->select('type', DB::raw('COUNT(*) AS product_count'))
                ->whereIn('type', $cate_ids)
                ->where('is_deleted', 0)
                ->groupBy('type')
                ->get();
            foreach ($count_rows as $row) {
                $count_map[(int)$row->type] = (int)$row->product_count;
            }
        }

        foreach ($data['data'] as $k => $row) {
            $cate_id = (int)$row['cate_id'];
            $data['data'][$k]['category_name'] = $category_map[$row['category_id']] ?? '';
            $data['data'][$k]['product_count'] = (int)($count_map[$cate_id] ?? 0);
        }

        return $data;
    }

    public function addCate(array $data)
    {
        $now = getDateTime();
        $data['add_time'] = $now;
        $data['update_time'] = $now;
        $data['is_deleted'] = 0;
        if (!isset($data['cate_enable'])) {
            $data['cate_enable'] = 1;
        }
        if (!isset($data['cate_sort'])) {
            $data['cate_sort'] = 50;
        }
        if (!isset($data['cate_type'])) {
            $data['cate_type'] = self::CATE_TYPE_CATEGORY;
        }
        if (!isset($data['is_system'])) {
            $data['is_system'] = 0;
        }

        return $this->repository->add($data);
    }

    public function editCate($cate_id, array $data)
    {
        $this->getExistCate($cate_id);
        unset($data['is_system'], $data['cate_type']);
        $data['update_time'] = getDateTime();
        return $this->repository->edit($cate_id, $data);
    }

    public function removeCate($cate_id)
    {
        $cate = $this->getExistCate($cate_id);
        if (!empty($cate['is_system'])) {
            throw new ErrorException('系统默认菜单不可删除');
        }

        DB::beginTransaction();
        try {
            $result = $this->repository->edit($cate_id, [
                'is_deleted' => 1,
                'cate_enable' => 0,
                'update_time' => getDateTime(),
            ]);
            if (!$result) {
                throw new ErrorException('删除失败');
            }

            $rows = $this->newProductPromoRepository->find([
                'type' => $cate_id,
                'is_deleted' => 0,
            ]) ?: [];
            $now = getDateTime();
            foreach ($rows as $row) {
                $this->newProductPromoRepository->edit($row['id'], [
                    'is_deleted' => 1,
                    'update_time' => $now,
                ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            if ($e instanceof ErrorException) {
                throw $e;
            }
            throw new ErrorException($e->getMessage() ?: '删除失败');
        }
    }

    public function editState($cate_id, $cate_enable)
    {
        $this->getExistCate($cate_id);
        return $this->repository->edit($cate_id, [
            'cate_enable' => (int)$cate_enable,
            'update_time' => getDateTime(),
        ]);
    }

    private function getExistCate($cate_id)
    {
        $cate = $this->repository->getOne($cate_id);
        if (!$cate || !empty($cate['is_deleted'])) {
            throw new ErrorException('推广分类不存在');
        }

        return $cate;
    }
}

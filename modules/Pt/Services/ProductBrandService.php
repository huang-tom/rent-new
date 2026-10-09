<?php

namespace Modules\Pt\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Pt\Repositories\Contracts\ProductBrandRepository;
use App\Exceptions\ErrorException;
use Modules\Pt\Repositories\Contracts\ProductCategoryRepository;
use Modules\Pt\Repositories\Contracts\ProductTypeRepository;

/**
 * Class ProductBrandService.
 *
 * @package Modules\Pt\Services
 */
class ProductBrandService extends BaseService
{
    private $productCategoryRepository;
    private $productTypeRepository;

    public function __construct(
        ProductBrandRepository    $productBrandRepository,
        ProductCategoryRepository $productCategoryRepository,
        ProductTypeRepository     $productTypeRepository
    )
    {
        $this->repository = $productBrandRepository;
        $this->productCategoryRepository = $productCategoryRepository;
        $this->productTypeRepository = $productTypeRepository;
    }


    /**
     * 品牌树形数据
     * @param $request
     * @return array
     */
    public function tree($request)
    {
        $conditions = [];
        if ($request->has('front')) {
            $conditions['brand_recommend'] = 1;
            $conditions['brand_enable'] = 1;
            if ($brand_name = $request->get('keywords')) {
                $conditions[] = ['brand_name', 'LIKE', '%' . $brand_name . '%'];
            }
        }

        $brand_lists = $this->repository->find($conditions);
        $category_ids = array_column($brand_lists, 'category_id');
        $categories = $this->productCategoryRepository->gets($category_ids);

        $brandList = [];
        foreach ($categories as $category) {
            $brands = [];
            foreach ($brand_lists as $brand) {
                if ($brand['category_id'] == $category['category_id']) {
                    $brands[] = $brand;
                }
            }

            if (count($brands) > 0) {
                $brandList[] = [
                    'children' => empty($brands) ? null : $brands,
                    'brand_id' => $category['category_id'],
                    'brand_name' => $category['category_name']
                ];
            }
        }

        return $brandList;
    }


    /**
     * 删除
     * @param $brand_id
     * @return bool
     * @throws ErrorException
     */
    public function remove($brand_id)
    {
        $tmp_rows = $this->productTypeRepository->find([['brand_ids', 'FIND_IN_SET', [$brand_id]]]);
        $count = count($tmp_rows);
        if ($count > 0) {
            throw new ErrorException(sprintf('有 %d 条类型使用，不可删除', $count));
        }

        $result = $this->repository->remove($brand_id);
        if ($result) {
            return true;
        } else {
            throw new ErrorException('删除失败');
        }
    }

}

<?php

namespace Modules\Pt\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Pt\Repositories\Contracts\ProductItemRepository;
use Modules\Pt\Repositories\Contracts\ProductSpecItemRepository;
use App\Exceptions\ErrorException;

/**
 * Class ProductSpecItemService.
 *
 * @package Modules\Pt\Services
 */
class ProductSpecItemService extends BaseService
{
    private $productItemRepository;

    public function __construct(ProductSpecItemRepository $productSpecItemRepository, ProductItemRepository $productItemRepository)
    {
        $this->repository = $productSpecItemRepository;
        $this->productItemRepository = $productItemRepository;
    }


    /**
     * 删除
     * @param $spec_item_id
     * @return bool
     * @throws ErrorException
     */
    public function remove($spec_item_id)
    {
        $rows = $this->productItemRepository->find([['spec_item_ids', 'FIND_IN_SET', [$spec_item_id]]]);
        $count = count($rows);
        if ($count) {
            throw new ErrorException(sprintf(__("已被 %d 个SKU商品使用，不可删除"), $count));
        }

        $result = $this->repository->remove($spec_item_id);
        if ($result) {
            return true;
        } else {
            throw new ErrorException(__('删除失败'));
        }
    }

}

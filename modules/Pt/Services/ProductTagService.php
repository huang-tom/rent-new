<?php

namespace Modules\Pt\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Pt\Repositories\Contracts\ProductIndexRepository;
use Modules\Pt\Repositories\Contracts\ProductTagRepository;
use App\Exceptions\ErrorException;

/**
 * Class ProductTagService.
 *
 * @package Modules\Pt\Services
 */
class ProductTagService extends BaseService
{

    private $productIndexRepository;

    public function __construct(ProductTagRepository $productTagRepository, ProductIndexRepository $productIndexRepository)
    {
        $this->repository = $productTagRepository;
        $this->productIndexRepository = $productIndexRepository;
    }


    /**
     * 删除
     * @param $product_tag_id
     * @return bool
     * @throws ErrorException
     */
    public function remove($product_tag_id)
    {
        $product_index_rows = $this->productIndexRepository->find([['product_tags', 'FIND_IN_SET', [$product_tag_id]]]);
        $count = count($product_index_rows);
        if ($count > 0) {
            throw new ErrorException(sprintf(__("标签 已经被 %d 个SPU商品使用，不可删除"), $count));
        }
        $result = $this->repository->remove($product_tag_id);

        if ($result) {
            return true;
        } else {
            throw new ErrorException(__('删除失败'));
        }
    }

}

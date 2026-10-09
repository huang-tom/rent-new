<?php

namespace Modules\Pt\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Pt\Repositories\Contracts\ProductInfoRepository;

/**
 * Class ProductInfoService.
 *
 * @package Modules\Pt\Services
 */
class ProductInfoService extends BaseService
{
    public function __construct(ProductInfoRepository $productInfoRepository)
    {
        $this->repository = $productInfoRepository;
    }

}

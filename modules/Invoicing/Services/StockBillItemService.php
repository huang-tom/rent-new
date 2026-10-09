<?php

namespace Modules\Invoicing\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Invoicing\Repositories\Contracts\StockBillItemRepository;

/**
 * Class StockBillItemService.
 *
 * @package Modules\Invoicing\Services
 */
class StockBillItemService extends BaseService
{
    public function __construct(StockBillItemRepository $stockBillItemRepository,)
    {
        $this->repository = $stockBillItemRepository;
    }

}

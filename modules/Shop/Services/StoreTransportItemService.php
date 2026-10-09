<?php

namespace Modules\Shop\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Shop\Repositories\Contracts\StoreTransportItemRepository;

/**
 * Class StoreTransportItemService.
 *
 * @package Modules\Shop\Services
 */
class StoreTransportItemService extends BaseService
{

    public function __construct(StoreTransportItemRepository $storeTransportItemRepository)
    {
        $this->repository = $storeTransportItemRepository;
    }

}

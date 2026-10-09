<?php

namespace Modules\Trade\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Trade\Repositories\Contracts\OrderStateLogRepository;

/**
 * Class OrderStateLogService.
 *
 * @package Modules\Trade\Services
 */
class OrderStateLogService extends BaseService
{

    public function __construct(OrderStateLogRepository $orderStateLogRepository)
    {
        $this->repository = $orderStateLogRepository;
    }

}

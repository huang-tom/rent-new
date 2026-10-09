<?php

namespace Modules\Pay\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Pay\Repositories\Contracts\DistributionCommissionRepository;

/**
 * Class DistributionCommissionService.
 *
 * @package Modules\Pay\Services
 */
class DistributionCommissionService extends BaseService
{

    public function __construct(DistributionCommissionRepository $distributionCommissionRepository)
    {
        $this->repository = $distributionCommissionRepository;
    }

}

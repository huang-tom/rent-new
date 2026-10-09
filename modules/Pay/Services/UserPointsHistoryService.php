<?php

namespace Modules\Pay\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Pay\Repositories\Contracts\UserPointsHistoryRepository;

/**
 * Class UserPointsHistoryService.
 *
 * @package Modules\Pay\Services
 */
class UserPointsHistoryService extends BaseService
{

    public function __construct(UserPointsHistoryRepository $userPointsHistoryRepository)
    {
        $this->repository = $userPointsHistoryRepository;
    }

}

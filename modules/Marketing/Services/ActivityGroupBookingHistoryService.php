<?php

namespace Modules\Marketing\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Marketing\Repositories\Contracts\ActivityGroupBookingHistoryRepository;

/**
 * Class ActivityGroupBookingHistoryService.
 *
 * @package Modules\Marketing\Services
 */
class ActivityGroupBookingHistoryService extends BaseService
{

    public function __construct(ActivityGroupBookingHistoryRepository $activityGroupBookingHistoryRepository)
    {
        $this->repository = $activityGroupBookingHistoryRepository;
    }

}

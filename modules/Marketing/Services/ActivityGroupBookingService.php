<?php

namespace Modules\Marketing\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Marketing\Repositories\Contracts\ActivityGroupBookingRepository;
use Modules\Marketing\Repositories\Contracts\ActivityGroupBookingHistoryRepository;
use Modules\Account\Repositories\Models\User;

/**
 * Class ActivityGroupBookingService.
 *
 * @package Modules\Marketing\Services
 */
class ActivityGroupBookingService extends BaseService
{

    private $activityGroupBookingHistoryRepository;

    public function __construct(
        ActivityGroupBookingRepository        $activityGroupBookingRepository,
        ActivityGroupBookingHistoryRepository $activityGroupBookingHistoryRepository
    )
    {
        $this->repository = $activityGroupBookingRepository;
        $this->activityGroupBookingHistoryRepository = $activityGroupBookingHistoryRepository;
    }

    public function fixUserGroupbookingInfo($data, $is_activity_arr)
    {
        return [];
    }

}

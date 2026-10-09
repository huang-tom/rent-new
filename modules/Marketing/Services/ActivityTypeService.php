<?php

namespace Modules\Marketing\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Marketing\Repositories\Contracts\ActivityTypeRepository;

/**
 * Class ActivityTypeService.
 *
 * @package Modules\Marketing\Services
 */
class ActivityTypeService extends BaseService
{

    public function __construct(ActivityTypeRepository $activityTypeRepository)
    {
        $this->repository = $activityTypeRepository;
    }

}

<?php

namespace Modules\Sys\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\LogActionRepository;

/**
 * Class LogActionService.
 *
 * @package Modules\Sys\Services
 */
class LogActionService extends BaseService
{

    public function __construct(LogActionRepository $logActionRepository)
    {
        $this->repository = $logActionRepository;
    }

}

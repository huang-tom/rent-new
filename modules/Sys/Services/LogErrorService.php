<?php

namespace Modules\Sys\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\LogErrorRepository;

/**
 * Class LogErrorService.
 *
 * @package Modules\Sys\Services
 */
class LogErrorService extends BaseService
{

    public function __construct(LogErrorRepository $logErrorRepository)
    {
        $this->repository = $logErrorRepository;
    }

}

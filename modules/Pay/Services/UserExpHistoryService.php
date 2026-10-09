<?php

namespace Modules\Pay\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Pay\Repositories\Contracts\UserExpHistoryRepository;

/**
 * Class UserExpHistoryService.
 *
 * @package Modules\Pay\Services
 */
class UserExpHistoryService extends BaseService
{

    public function __construct(UserExpHistoryRepository $userExpHistoryRepository)
    {
        $this->repository = $userExpHistoryRepository;
    }

}

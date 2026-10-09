<?php

namespace Modules\Account\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserBindConnectRepository;

/**
 * Class UserBindConnectService.
 *
 * @package Modules\Account\Services
 */
class UserBindConnectService extends BaseService
{
    public function __construct(UserBindConnectRepository $userBindConnectRepository)
    {
        $this->repository = $userBindConnectRepository;
    }

}

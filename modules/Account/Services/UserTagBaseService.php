<?php

namespace Modules\Account\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserTagBaseRepository;

/**
 * Class UserTagBaseService.
 *
 * @package Modules\Account\Services
 */
class UserTagBaseService extends BaseService
{

    public function __construct(UserTagBaseRepository $userTagBaseRepository)
    {
        $this->repository = $userTagBaseRepository;
    }

}

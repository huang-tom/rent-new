<?php

namespace Modules\Account\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserRepository;

class UserService extends BaseService
{

    public function __construct(UserRepository $userRepository)
    {
        $this->repository = $userRepository;
    }

}

<?php

namespace Modules\Admin\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Admin\Repositories\Contracts\UserAdminRepository;

/**
 * Class UserAdminService.
 *
 * @package Modules\Admin\Services
 */
class UserAdminService extends BaseService
{

    public function __construct(UserAdminRepository $userAdminRepository)
    {
        $this->repository = $userAdminRepository;
    }

}

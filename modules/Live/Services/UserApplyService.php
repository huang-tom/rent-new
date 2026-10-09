<?php

namespace Modules\Live\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Live\Repositories\Contracts\UserApplyRepository;

/**
 * Class UserApplyService.
 *
 * @package Modules\Live\Services
 */
class UserApplyService extends BaseService
{

    public function __construct(UserApplyRepository $userApplyRepository)
    {
        $this->repository = $userApplyRepository;
    }

}

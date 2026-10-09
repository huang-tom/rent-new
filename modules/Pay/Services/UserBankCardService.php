<?php

namespace Modules\Pay\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Pay\Repositories\Contracts\UserBankCardRepository;

/**
 * Class UserBankCardService.
 *
 * @package Modules\Pay\Services
 */
class UserBankCardService extends BaseService
{

    public function __construct(UserBankCardRepository $userBankCardRepository)
    {
        $this->repository = $userBankCardRepository;
    }

}

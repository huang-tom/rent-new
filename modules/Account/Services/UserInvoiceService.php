<?php

namespace Modules\Account\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserInvoiceRepository;

/**
 * Class UserInvoiceService.
 *
 * @package Modules\Account\Services
 */
class UserInvoiceService extends BaseService
{

    public function __construct(UserInvoiceRepository $userInvoiceRepository)
    {
        $this->repository = $userInvoiceRepository;
    }

}

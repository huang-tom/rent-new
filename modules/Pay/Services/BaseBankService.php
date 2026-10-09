<?php

namespace Modules\Pay\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Pay\Repositories\Contracts\BaseBankRepository;

/**
 * Class BaseBankService.
 *
 * @package Modules\Pay\Services
 */
class BaseBankService extends BaseService
{

    public function __construct(BaseBankRepository $baseBankRepository)
    {
        $this->repository = $baseBankRepository;
    }

}

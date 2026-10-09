<?php

namespace Modules\Sys\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\ContractTypeRepository;

/**
 * Class ContractTypeService.
 *
 * @package Modules\Sys\Services
 */
class ContractTypeService extends BaseService
{

    public function __construct(ContractTypeRepository $contractTypeRepository)
    {
        $this->repository = $contractTypeRepository;
    }

}

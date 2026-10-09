<?php

namespace Modules\O2o\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\O2o\Repositories\Contracts\ChainItemRepository;


/**
 * Class ChainItemService.
 *
 * @package Modules\O2o\Services
 */
class ChainItemService extends BaseService
{

    public function __construct(ChainItemRepository $chainItemRepository)
    {
        $this->repository = $chainItemRepository;
    }

}

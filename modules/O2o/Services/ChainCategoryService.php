<?php

namespace Modules\O2o\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\O2o\Repositories\Contracts\ChainCategoryRepository;


/**
 * Class ChainCategoryService.
 *
 * @package Modules\O2o\Services
 */
class ChainCategoryService extends BaseService
{

    public function __construct(ChainCategoryRepository $chainCategoryRepository)
    {
        $this->repository = $chainCategoryRepository;
    }

}

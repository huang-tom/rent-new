<?php

namespace Modules\Sys\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\PagePcNavRepository;

/**
 * Class PagePcNavService.
 *
 * @package Modules\Sys\Services
 */
class PagePcNavService extends BaseService
{

    public function __construct(PagePcNavRepository $pagePcNavRepository)
    {
        $this->repository = $pagePcNavRepository;
    }

}

<?php

namespace Modules\Sys\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\LangMetaRepository;

/**
 * Class LangMetaService.
 *
 * @package Modules\Sys\Services
 */
class LangMetaService extends BaseService
{

    public function __construct(LangMetaRepository $langMetaRepository)
    {
        $this->repository = $langMetaRepository;
    }

}

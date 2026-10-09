<?php

namespace Modules\Sys\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\MaterialGalleryRepository;

class MaterialGalleryService extends BaseService
{

    public function __construct(MaterialGalleryRepository $materialGalleryRepository)
    {
        $this->repository = $materialGalleryRepository;
    }

}

<?php

namespace Modules\Pt\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Pt\Repositories\Contracts\ProductCommentHelpfulRepository;

/**
 * Class ProductCommentHelpfulService.
 *
 * @package Modules\Pt\Services
 */
class ProductCommentHelpfulService extends BaseService
{

    public function __construct(ProductCommentHelpfulRepository $productCommentHelpfulRepository)
    {
        $this->repository = $productCommentHelpfulRepository;
    }

}

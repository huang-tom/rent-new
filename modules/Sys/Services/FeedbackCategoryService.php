<?php

namespace Modules\Sys\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\FeedbackCategoryRepository;

/**
 * Class FeedbackCategoryService.
 *
 * @package Modules\Sys\Services
 */
class FeedbackCategoryService extends BaseService
{
    public function __construct(FeedbackCategoryRepository $feedbackCategoryRepository)
    {
        $this->repository = $feedbackCategoryRepository;
    }

}

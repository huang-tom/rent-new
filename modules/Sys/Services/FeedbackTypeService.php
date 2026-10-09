<?php

namespace Modules\Sys\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\FeedbackTypeRepository;

/**
 * Class FeedbackTypeService.
 *
 * @package Modules\Sys\Services
 */
class FeedbackTypeService extends BaseService
{

    public function __construct(FeedbackTypeRepository $feedbackTypeRepository)
    {
        $this->repository = $feedbackTypeRepository;
    }

}

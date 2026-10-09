<?php

namespace Modules\Pay\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Pay\Repositories\Contracts\ConsumeRecordRepository;

/**
 * Class ConsumeRecordService.
 *
 * @package Modules\Pay\Services
 */
class ConsumeRecordService extends BaseService
{

    public function __construct(ConsumeRecordRepository $consumeRecordRepository)
    {
        $this->repository = $consumeRecordRepository;
    }

}

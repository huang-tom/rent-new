<?php

namespace Modules\Trade\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Trade\Repositories\Contracts\OrderBaseRepository;
use Modules\Trade\Repositories\Criteria\OrderBaseCriteria;

/**
 * Class OrderBaseService.
 *
 * @package Modules\Trade\Services
 */
class OrderBaseService extends BaseService
{

    public function __construct(OrderBaseRepository $orderBaseRepository)
    {
        $this->repository = $orderBaseRepository;
    }


    /**
     * 获取列表
     * @return array
     */
    public function getLists($request)
    {
        $limit = $request->get('size') ?? 10;
        $data = $this->repository->list(new OrderBaseCriteria($request), $limit);

        return $data;
    }

}

<?php

namespace Modules\Sys\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\DictItemRepository;
use App\Exceptions\ErrorException;
use Modules\Sys\Repositories\Criteria\DictItemCriteria;

/**
 * Class DictItemService.
 *
 * @package Modules\Sys\Services
 */
class DictItemService extends BaseService
{

    public function __construct(DictItemRepository $dictItemRepository)
    {
        $this->repository = $dictItemRepository;
    }


    /**
     * 删除
     * @param $dict_item_id
     * @return bool
     * @throws ErrorException
     */
    public function remove($dict_item_id)
    {
        $row = $this->repository->getOne($dict_item_id);
        if ($row['dict_item_buildin']) {
            throw new ErrorException(__('系统内置，不可删除！'));
        }

        $result = $this->repository->remove($dict_item_id);

        if ($result) {
            return true;
        } else {
            throw new ErrorException(__('删除失败'));
        }

    }

}

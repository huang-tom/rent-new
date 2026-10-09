<?php

namespace Modules\Sys\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\DictBaseRepository;
use App\Exceptions\ErrorException;

/**
 * Class DictBaseService.
 *
 * @package Modules\Sys\Services
 */
class DictBaseService extends BaseService
{

    public function __construct(DictBaseRepository $dictBaseRepository)
    {
        $this->repository = $dictBaseRepository;
    }


    /**
     * 删除
     * @param $dict_id
     * @return bool
     * @throws ErrorException
     */
    public function remove($dict_id)
    {
        $row = $this->repository->getOne($dict_id);
        if ($row['dict_buildin']) {
            throw new ErrorException(__('系统内置，不可删除！'));
        }

        $result = $this->repository->remove($dict_id);

        if ($result) {
            return true;
        } else {
            throw new ErrorException(__('删除失败'));
        }
    }

}

<?php

namespace Modules\Sys\Services;

use App\Exceptions\ErrorException;
use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;
use Modules\Sys\Repositories\Contracts\ConfigTypeRepository;

/**
 * Class ConfigTypeService.
 *
 * @package Modules\Sys\Services
 */
class ConfigTypeService extends BaseService
{

    private $configBaseRepository;

    public function __construct(ConfigTypeRepository $configTypeRepository, ConfigBaseRepository $configBaseRepository)
    {
        $this->repository = $configTypeRepository;
        $this->configBaseRepository = $configBaseRepository;
    }


    /**
     * 删除
     * @param $config_type_id
     * @return bool
     * @throws ErrorException
     */
    public function removeType($config_type_id)
    {
        $type_row = $this->repository->getOne($config_type_id);
        if ($type_row['config_type_buildin']) {
            throw new ErrorException(__('系统内置，不可删除'));
        }

        $tmp_rows = $this->configBaseRepository->find(['config_type_id' => $config_type_id]);
        $count = count($tmp_rows);
        if ($count > 0) {
            throw new ErrorException(sprintf(__('分组下有 %d 条配置信息，不可删除'), $count));
        }

        $result = $this->repository->remove($config_type_id);
        if ($result) {
            return true;
        } else {
            throw new ErrorException(__('删除失败'));
        }
    }

}

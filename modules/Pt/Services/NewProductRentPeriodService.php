<?php

namespace Modules\Pt\Services;

use App\Exceptions\ErrorException;
use Kuteshop\Core\Service\BaseService;
use Modules\Pt\Repositories\Contracts\NewProductRentPeriodRepository;
use Modules\Pt\Repositories\Contracts\NewProductRentPriceRepository;

/**
 * Class NewProductRentPeriodService.
 *
 * @package Modules\Pt\Services
 */
class NewProductRentPeriodService extends BaseService
{
    private $newProductRentPriceRepository;

    public function __construct(
        NewProductRentPeriodRepository $newProductRentPeriodRepository,
        NewProductRentPriceRepository  $newProductRentPriceRepository
    )
    {
        $this->repository = $newProductRentPeriodRepository;
        $this->newProductRentPriceRepository = $newProductRentPriceRepository;
    }

    /**
     * 新增档位
     * @param array $data
     * @return mixed
     */
    public function addPeriod(array $data)
    {
        $now = getDateTime();
        $data['add_time'] = $now;
        $data['update_time'] = $now;
        $data['is_deleted'] = 0;
        if (!isset($data['period_enable'])) {
            $data['period_enable'] = 1;
        }
        if (!isset($data['period_order'])) {
            $data['period_order'] = 50;
        }

        return $this->repository->add($data);
    }

    /**
     * 编辑档位
     * @param int $period_id
     * @param array $data
     * @return mixed
     * @throws ErrorException
     */
    public function editPeriod($period_id, array $data)
    {
        $this->getExistPeriod($period_id);
        $data['update_time'] = getDateTime();
        return $this->repository->edit($period_id, $data);
    }

    /**
     * 删除档位（软删除，保留历史关联可回显）
     * @param int $period_id
     * @return bool
     * @throws ErrorException
     */
    public function removePeriod($period_id)
    {
        $this->getExistPeriod($period_id);

        $result = $this->repository->edit($period_id, [
            'is_deleted' => 1,
            'period_enable' => 0,
            'update_time' => getDateTime(),
        ]);
        if ($result) {
            return true;
        }
        throw new ErrorException('删除失败');
    }

    /**
     * 修改启用状态
     * @param int $period_id
     * @param int $period_enable
     * @return mixed
     * @throws ErrorException
     */
    public function editState($period_id, $period_enable)
    {
        $this->getExistPeriod($period_id);

        return $this->repository->edit($period_id, [
            'period_enable' => (int)$period_enable,
            'update_time' => getDateTime(),
        ]);
    }

    /**
     * @param int $period_id
     * @return array
     * @throws ErrorException
     */
    private function getExistPeriod($period_id)
    {
        $period = $this->repository->getOne($period_id);
        if (!$period || !empty($period['is_deleted'])) {
            throw new ErrorException('租期档位不存在');
        }

        return $period;
    }
}

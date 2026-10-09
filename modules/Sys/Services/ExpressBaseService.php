<?php

namespace Modules\Sys\Services;

use App\Exceptions\ErrorException;
use Kuteshop\Core\Service\BaseService;
use Modules\Shop\Repositories\Contracts\StoreExpressLogisticsRepository;
use Modules\Sys\Repositories\Contracts\ExpressBaseRepository;

/**
 * Class ExpressBaseService.
 *
 * @package Modules\Sys\Services
 */
class ExpressBaseService extends BaseService
{
    private $storeExpressLogisticsRepository;

    public function __construct(ExpressBaseRepository $expressBaseRepository, StoreExpressLogisticsRepository $storeExpressLogisticsRepository)
    {
        $this->repository = $expressBaseRepository;
        $this->storeExpressLogisticsRepository = $storeExpressLogisticsRepository;
    }


    /**
     * 删除
     * @param $request
     * @return bool
     * @throws ErrorException
     */
    public function removeExpress($request)
    {
        $express_logistics = $this->storeExpressLogisticsRepository->find(['express_id' => $request['express_id']]);
        if (!empty($express_logistics)) {
            throw new ErrorException(sprintf(__("有 %d 条物流使用，不可删除"), count($express_logistics)));
        }

        $result = $this->repository->remove($request['express_id']);
        if ($result) {
            return true;
        } else {
            throw new ErrorException(__('删除失败'));
        }
    }


    /**
     * 批量删除
     *
     * [新增 2026-09-22] 前端「快递公司」页的「批量删除」按钮调
     * POST /manage/sys/expressBase/removeBatch，后端原本没有这条路由 → 404。
     *
     * 实现上**逐条复用上面的 removeExpress()**，而不是自己写 whereIn()->delete()：
     * 因为单删里有「有物流在使用则不可删除」的业务校验，复用可保证
     * 「批量删」与「单条删」的判定完全一致，不会出现批量能删、单条删不掉的口径差异。
     * 快递公司数量级很小（几十条），逐条查询的代价可忽略。
     *
     * @param $request
     * @return bool
     * @throws ErrorException
     */
    public function removeExpressBatch($request)
    {
        $ids = $request['express_id'];
        $ids = is_array($ids) ? $ids : explode(',', (string)$ids);
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            throw new ErrorException(__('请选择要删除的数据'));
        }

        foreach ($ids as $express_id) {
            $this->removeExpress(['express_id' => $express_id]);
        }

        return true;
    }

}

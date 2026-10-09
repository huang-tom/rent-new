<?php

namespace Modules\Marketing\Services;

use App\Support\StateCode;
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserInfoRepository;
use Modules\Account\Repositories\Contracts\UserLevelRepository;
use Modules\Marketing\Repositories\Contracts\ActivityBaseRepository;
use App\Exceptions\ErrorException;
use Modules\Marketing\Repositories\Contracts\ActivityItemRepository;
use Modules\Marketing\Repositories\Criteria\ActivityBaseCriteria;
use Modules\Pt\Repositories\Contracts\ProductBaseRepository;
use Modules\Pt\Repositories\Contracts\ProductIndexRepository;
use Modules\Pt\Repositories\Contracts\ProductItemRepository;
use Modules\Shop\Repositories\Contracts\UserVoucherNumRepository;


/**
 * Class ActivityBaseService.
 *
 * @package Modules\Marketing\Services
 */
class ActivityBaseService extends BaseService
{

    private $activityItemRepository;
    private $userVoucherNumRepository;
    private $userLevelRepository;
    private $userInfoRepository;
    private $productItemRepository;
    private $productBaseRepository;
    private $productIndexRepository;

    public function __construct(
        ActivityBaseRepository   $activityBaseRepository,
        ActivityItemRepository   $activityItemRepository,
        UserVoucherNumRepository $userVoucherNumRepository,
        UserLevelRepository      $userLevelRepository,
        UserInfoRepository       $userInfoRepository,
        ProductItemRepository    $productItemRepository,
        ProductBaseRepository    $productBaseRepository,
        ProductIndexRepository   $productIndexRepository
    )
    {
        $this->repository = $activityBaseRepository;
        $this->activityItemRepository = $activityItemRepository;
        $this->userVoucherNumRepository = $userVoucherNumRepository;
        $this->userLevelRepository = $userLevelRepository;
        $this->userInfoRepository = $userInfoRepository;
        $this->productItemRepository = $productItemRepository;
        $this->productBaseRepository = $productBaseRepository;
        $this->productIndexRepository = $productIndexRepository;
    }


    /**
     * 获取列表
     */
    public function list($request, $criteria)
    {
        $limit = $request->get('size') ?? 10;
        $data = $this->repository->list($criteria, $limit);
        $data['limit'] = $limit;

        return $data;
    }

    public function checkActivityState($activity_base): array
    {
        return $activity_base;
    }

    public function getActivityItems($activity_ids): array
    {
        $activity_product_items = [];
        return $activity_product_items;
    }

    /**
     * 修改活动状态（上下架 / 排序 / 结束标记）
     *
     * [本地补齐 2026-09-22] 厂商原始实现是空壳 `return true;`，
     * 前端点「上架/下架」接口返回 200 但数据库毫无变化（静默失效）。
     * 这里按 ProductBaseService::editState() 的同构写法补齐：
     *   只更新请求中实际出现的字段，未传的字段不动。
     */
    public function editState($request)
    {
        $activity_id = $request->get('activity_id');
        $state_data = [];

        if ($request->has('activity_state')) {
            $state_data['activity_state'] = (int)$request['activity_state'];
        }
        if ($request->has('activity_sort')) {
            $state_data['activity_sort'] = (int)$request['activity_sort'];
        }
        if ($request->has('activity_is_finish')) {
            $state_data['activity_is_finish'] = (int)$request['activity_is_finish'];
        }

        if (!$activity_id) {
            throw new ErrorException(__('活动编号不能为空'));
        }
        if (empty($state_data)) {
            throw new ErrorException(__('没有需要更新的状态字段'));
        }

        $result = $this->repository->edit($activity_id, $state_data);
        if (!$result) {
            throw new ErrorException(__('更新失败'));
        }

        return true;
    }

    public function editActivityBase($activity_id, $activity_base)
    {
        return true;
    }

    public function editActivityTypeIds($activity_base, $product_ids)
    {

        $flag_row = [];
        return is_ok($flag_row);
    }

    public function listVoucher($request, $user_id)
    {
        $limit = $request->get('size') ?? 10;
        $data = $this->repository->list(new ActivityBaseCriteria($request), $limit);

        return $data;
    }

    public function updateActivityState()
    {
        return true;
    }

}

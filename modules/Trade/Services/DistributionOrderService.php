<?php

namespace Modules\Trade\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Trade\Repositories\Contracts\DistributionOrderRepository;
use App\Exceptions\ErrorException;
use Illuminate\Http\Request;
use Modules\Account\Repositories\Contracts\UserInfoRepository;

/**
 * Class DistributionOrderService.
 *
 * @package Modules\Trade\Services
 */
class DistributionOrderService extends BaseService
{
    private $userInfoRepository;


    public function __construct(DistributionOrderRepository $distributionOrderRepository, UserInfoRepository $userInfoRepository)
    {
        $this->repository = $distributionOrderRepository;
        $this->userInfoRepository = $userInfoRepository;
    }


    /**
     * 获取列表
     * @return array
     */
    public function list($request, $criteria)
    {
        $limit = $request->get('size') ?? 10;
        $data = $this->repository->list($criteria, $limit);

        //加入用户昵称
        $data['data'] = $this->userInfoRepository->fixUserInfo($data['data'], [
            'buyer_user_name' => 'user_nickname',
            'buyer_user_avatar' => 'user_avatar'
        ], 'buyer_user_id');

        return $data;
    }


    /**
     * 用户基础信息-用户来源关系记录，此记录不可以改变。列表数据
     * @param $request
     * @return bool
     * @throws ErrorException
     */
    public function listsOrder(Request $request)
    {
        $time_flag = $request->input('time_flag');
        if ($uo_level = $request->input('uo_level')) {
            if ($uo_level == 1) {
                $request['uo_levels'] = array(
                    1,
                    2,
                    3,
                    11,
                    12,
                    13
                );

            } else if ($uo_level == 81) {
                $request['uo_levels'] = array(
                    14,
                    15,
                    16,
                    4,
                    5,
                    6
                );
            } else {
                $request['uo_level'] = $uo_level;
            }
        }

        if (1 == $time_flag) {
            $time_section = getToday();

            $request['uo_time_start'] = $time_section['start'];
            $request['uo_time_end'] = $time_section['end'];
            $time = $time_section['start'];
        } elseif (2 == $time_flag) {
            $time_section = getSubDaysRange(30);
            $request['uo_time_start'] = $time_section['start'];
            $request['uo_time_end'] = $time_section['end'];
            $time = $time_section['start'];
        } elseif (3 == $time_flag) {
            $time_section = getSubDaysRange(90);
            $request['uo_time_start'] = $time_section['start'];
            $request['uo_time_end'] = $time_section['end'];
            $time = $time_section['start'];
        } else {
            $time = null;
        }

        $request['uo_is_paid'] = 1;

        $lists = $this->list($request);

        $data = [];
        $uo_buy_commission_total = 0.00;

        if ($lists['data']) {
            $uo_buy_commission_row = $this->repository->calCommissionByTime($request['user_id'], $uo_level, $time);

            if ($uo_buy_commission_row) {
                $uo_buy_commission_row = (array)$uo_buy_commission_row[0];
                //计算总金额
                $uo_buy_commission_total = sprintf('%.2f', $uo_buy_commission_row['uo_buy_commission']);
            }
        }

        $data['items']['records'] = $lists['data'];
        $data['items']['current'] = $lists['current_page'];
        $data['items']['pages'] = $lists['last_page'];
        $data['items']['size'] = $lists['limit'];
        $data['items']['total'] = $lists['total'];

        $data['uo_buy_commission_total'] = $uo_buy_commission_total;

        return $data;
    }
}

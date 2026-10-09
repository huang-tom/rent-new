<?php

namespace Modules\Analytics\Services;

use Modules\Analytics\Repositories\Models\AnalyticsUser;

/**
 * Class AnalyticsUserService.
 *
 * @package Modules\Analytics\Services
 */
class AnalyticsUserService
{
    private $analyticsUser;

    public function __construct(AnalyticsUser $analyticsUser)
    {
        $this->analyticsUser = $analyticsUser;
    }


    /**
     * getRegUser
     * @return array
     */
    public function getRegUser()
    {
        $today = getToday();
        $data['today'] = $this->analyticsUser->getRegUserNum($today['start'], $today['end']);

        $yesterday = getYesterday();
        $data['yestoday'] = $this->analyticsUser->getRegUserNum($yesterday['start'], $yesterday['end']);

        // 计算日环比 日环比 = (当日数据 - 前一日数据) / 前一日数据
        $daym2m = 0;
        if ($data['yestoday']) {
            $daym2m = ($data['today'] - $data['yestoday']) / $data['yestoday'];
        }
        $data['daym2m'] = $daym2m;

        $month = getMonth();
        $data['month'] = $this->analyticsUser->getRegUserNum($month['start'], $month['end']);

        return $data;
    }


    public function getUserTimeLine($stime, $etime)
    {
        $data = $this->analyticsUser->getUserTimeLine($stime, $etime);

        return $data;
    }


    public function getUserNum($request)
    {
        $data = [];
        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');

        // 获取当前周期内数据
        $current = $this->analyticsUser->getRegUserNum($stime, $etime);
        $data['current'] = $current;
        $data['pre'] = 0;
        $data['daym2m'] = 0;

        // 获取上个周期的数据
        if ($stime && $etime) {
            // 计算上个周期的时间范围
            $pre_stime = $stime - ($etime - $stime);
            $pre_etime = $stime;
            $pre_reg_num = $this->analyticsUser->getRegUserNum($pre_stime, $pre_etime);
            if ($pre_reg_num) {
                $data['pre'] = $pre_reg_num;
                $daym2m = (($current - $pre_reg_num) / $pre_reg_num);
                $data['daym2m'] = $daym2m;
            }
        }

        return $data;
    }

}

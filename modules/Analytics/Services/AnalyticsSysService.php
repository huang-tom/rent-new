<?php

namespace Modules\Analytics\Services;

use Modules\Analytics\Repositories\Models\AnalyticsSys;

/**
 * Class AnalyticsSysService.
 *
 * @package Modules\Analytics\Services
 */
class AnalyticsSysService
{
    private $analyticsSys;

    public function __construct(AnalyticsSys $analyticsSys)
    {
        $this->analyticsSys = $analyticsSys;
    }


    /**
     * 用户访问量
     * @return array
     */
    public function getVisitor()
    {
        $today = getToday();
        $data['today'] = $this->analyticsSys->getVisitorNum($today['start'], $today['end']);

        $yesterday = getYesterday();
        $data['yestoday'] = $this->analyticsSys->getVisitorNum($yesterday['start'], $yesterday['end']);

        // 计算日环比 日环比 = (当日数据 - 前一日数据) / 前一日数据
        $daym2m = 0;
        if ($data['yestoday']) {
            $daym2m = ($data['today'] - $data['yestoday']) / $data['yestoday'];
        }
        $data['daym2m'] = $daym2m;

        $month = getMonth();
        $data['month'] = $this->analyticsSys->getVisitorNum($month['start'], $month['end']);

        return $data;
    }


    public function getAccessNum($request)
    {

        $data = [
            'current' => 0,
            'pre' => 0,
            'daym2m' => 0
        ];

        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');
        $data['current'] = $current = $this->analyticsSys->getAccessNum($stime, $etime);

        // 获取上个周期的数据
        if ($stime && $etime) {
            // 计算上个周期的时间范围
            $pre_stime = $stime - ($etime - $stime);
            $pre_etime = $stime;
            $pre_reg_num = $this->analyticsSys->getAccessNum($pre_stime, $pre_etime);
            if ($pre_reg_num) {
                $data['pre'] = $pre_reg_num;
                $daym2m = (($current - $pre_reg_num) / $pre_reg_num);
                $data['daym2m'] = $daym2m;
            }
        }

        return $data;
    }


    public function getAccessVisitorTimeLine($request)
    {
        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');
        $data = $this->analyticsSys->getAccessVisitorTimeLine($stime, $etime);

        return $data;
    }

    public function getAccessItemUserTimeLine($request)
    {
        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');
        $item_id = $request->input('item_id', 0);
        $data = $this->analyticsSys->getAccessItemUserTimeLine($stime, $etime, $item_id);

        return $data;
    }

    public function listAccessItem($request)
    {
        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');
        $item_id = $request->input('item_id', 0);
        $data = $this->analyticsSys->listAccessItem($stime, $etime, $item_id);

        return $data;
    }


    public function getAccessItemTimeLine($request)
    {
        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');
        $item_id = $request->input('item_id', 0);
        $data = $this->analyticsSys->getAccessItemTimeLine($stime, $etime, $item_id);

        return $data;
    }


    public function getAccessVisitorNum($request)
    {
        $data = [
            'pre' => 0,
            'daym2m' => 0
        ];

        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');
        $data['current'] = $current = $this->analyticsSys->getVisitorNum($stime, $etime);

        // 获取上个周期的数据
        if ($stime && $etime) {
            // 计算上个周期的时间范围
            $pre_stime = $stime - ($etime - $stime);
            $pre_etime = $stime;
            $pre_reg_num = $this->analyticsSys->getVisitorNum($pre_stime, $pre_etime);
            if ($pre_reg_num) {
                $data['pre'] = $pre_reg_num;
                $daym2m = (($current - $pre_reg_num) / $pre_reg_num);
                $data['daym2m'] = $daym2m;
            }
        }

        return $data;
    }

    public function getAccessItemNum($request)
    {
        $data = [
            'pre' => 0,
            'daym2m' => 0
        ];

        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');
        $item_id = $request->input('item_id', 0);
        $data['current'] = $current = $this->analyticsSys->getAccessItemNum($stime, $etime, $item_id);

        // 获取上个周期的数据
        if ($stime && $etime) {
            // 计算上个周期的时间范围
            $pre_stime = $stime - ($etime - $stime);
            $pre_etime = $stime;
            $pre_reg_num = $this->analyticsSys->getAccessItemNum($pre_stime, $pre_etime, $item_id);
            if ($pre_reg_num) {
                $data['pre'] = $pre_reg_num;
                $daym2m = (($current - $pre_reg_num) / $pre_reg_num);
                $data['daym2m'] = $daym2m;
            }
        }

        return $data;
    }


    public function getAccessItemUserNum($request)
    {
        $data = [
            'pre' => 0,
            'daym2m' => 0
        ];

        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');
        $item_id = $request->input('item_id', 0);
        $data['current'] = $current = $this->analyticsSys->getAccessItemUserNum($stime, $etime, $item_id);

        // 获取上个周期的数据
        if ($stime && $etime) {
            // 计算上个周期的时间范围
            $pre_stime = $stime - ($etime - $stime);
            $pre_etime = $stime;
            $pre_reg_num = $this->analyticsSys->getAccessItemUserNum($pre_stime, $pre_etime, $item_id);
            if ($pre_reg_num) {
                $data['pre'] = $pre_reg_num;
                $daym2m = (($current - $pre_reg_num) / $pre_reg_num);
                $data['daym2m'] = $daym2m;
            }
        }

        return $data;
    }
}

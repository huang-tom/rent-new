<?php

namespace Modules\Analytics\Services;

use App\Support\StateCode;
use Modules\Analytics\Repositories\Models\AnalyticsReturn;

/**
 * Class AnalyticsReturnService.
 *
 * @package Modules\Analytics\Services
 */
class AnalyticsReturnService
{
    private $analyticsReturn;
    private $return_state_ids = [];

    public function __construct(AnalyticsReturn $analyticsReturn)
    {
        $this->analyticsReturn = $analyticsReturn;
        $this->return_state_ids = [
            StateCode::RETURN_PROCESS_FINISH,
            StateCode::RETURN_PROCESS_CHECK,
            StateCode::RETURN_PROCESS_RECEIVED,
            StateCode::RETURN_PROCESS_REFUND,
            StateCode::RETURN_PROCESS_RECEIPT_CONFIRMATION,
            StateCode::RETURN_PROCESS_REFUSED,
            StateCode::RETURN_PROCESS_SUBMIT
        ];
    }


    /**
     * @param $request
     * @return array
     * @throws \App\Exceptions\ErrorException
     */
    public function getReturnNum($request)
    {
        $data = [];
        $data['pre'] = 0;
        $data['daym2m'] = 0;

        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');

        // 获取当前周期内数据
        $current = $this->analyticsReturn->getReturnNum($stime, $etime, $this->return_state_ids);
        $data['current'] = $current;

        // 获取上个周期的数据
        if ($stime && $etime) {
            // 计算上个周期的时间范围
            $pre_stime = $stime - ($etime - $stime);
            $pre_etime = $stime;
            $pre_reg_num = $this->analyticsReturn->getReturnNum($pre_stime, $pre_etime, $this->return_state_ids);
            if ($pre_reg_num) {
                $data['pre'] = $pre_reg_num;
                $daym2m = (($current - $pre_reg_num) / $pre_reg_num);
                $data['daym2m'] = $daym2m;
            }
        }

        return $data;
    }


    /**
     * @param $request
     * @return array
     * @throws \App\Exceptions\ErrorException
     */
    public function getReturnAmount($request)
    {
        $data = [];
        $data['pre'] = 0;
        $data['daym2m'] = 0;

        $stime = $request->input('stime');
        $etime = $request->input('etime');

        // 获取当前周期内数据
        $current = $this->analyticsReturn->getReturnAmount($stime, $etime, $this->return_state_ids);
        $data['current'] = $current;

        // 获取上个周期的数据
        if ($stime && $etime) {
            // 计算上个周期的时间范围
            $pre_stime = $stime - ($etime - $stime);
            $pre_etime = $stime;
            $pre_reg_num = $this->analyticsReturn->getReturnAmount($pre_stime, $pre_etime, $this->return_state_ids);
            if ($pre_reg_num) {
                $data['pre'] = $pre_reg_num;
                $daym2m = (($current - $pre_reg_num) / $pre_reg_num);
                $data['daym2m'] = $daym2m;
            }
        }

        return $data;
    }


    public function getReturnAmountTimeline($request)
    {
        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');
        $data = $this->analyticsReturn->getReturnAmountTimeline($stime, $etime, $this->return_state_ids);

        return $data;
    }


    public function getReturnNumTimeline($request)
    {
        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');
        $data = $this->analyticsReturn->getReturnTimeLine($stime, $etime, $this->return_state_ids);

        return $data;
    }


}

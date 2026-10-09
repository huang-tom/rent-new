<?php

namespace Modules\Analytics\Services;

use App\Support\StateCode;
use Modules\Analytics\Repositories\Models\AnalyticsOrder;
use Modules\Analytics\Repositories\Models\AnalyticsProduct;
use Modules\Analytics\Repositories\Models\AnalyticsUser;

/**
 * Class AnalyticsOrderService.
 *
 * @package Modules\Analytics\Services
 */
class AnalyticsOrderService
{
    private $analyticsOrder;
    private $analyticsUser;
    private $analyticsProduct;

    public function __construct(AnalyticsOrder $analyticsOrder, AnalyticsUser $analyticsUser, AnalyticsProduct $analyticsProduct)
    {
        $this->analyticsOrder = $analyticsOrder;
        $this->analyticsUser = $analyticsUser;
        $this->analyticsProduct = $analyticsProduct;
    }


    /**
     * 获取订单数量
     * @return array
     */
    public function getOrderNum()
    {
        $today = getToday();

        //统计没有取消的订单
        $order_state_ids = [
            StateCode::ORDER_STATE_WAIT_PAY,
            StateCode::ORDER_STATE_WAIT_PAID,
            StateCode::ORDER_STATE_WAIT_REVIEW,
            StateCode::ORDER_STATE_WAIT_FINANCE_REVIEW,
            StateCode::ORDER_STATE_PICKING,
            StateCode::ORDER_STATE_WAIT_SHIPPING,
            StateCode::ORDER_STATE_SHIPPED,
            StateCode::ORDER_STATE_RECEIVED,
            StateCode::ORDER_STATE_FINISH,
            StateCode::ORDER_STATE_SELF_PICKUP
        ];
        $order_is_paids = [StateCode::ORDER_PAID_STATE_PART, StateCode::ORDER_PAID_STATE_YES];

        $data['today'] = $this->analyticsOrder->getOrderNum($today['start'], $today['end'], $order_state_ids, $order_is_paids);

        $yesterday = getYesterday();
        $data['yestoday'] = $this->analyticsOrder->getOrderNum($yesterday['start'], $yesterday['end'], $order_state_ids, $order_is_paids);

        // 计算日环比 日环比 = (当日数据 - 前一日数据) / 前一日数据 * 100%
        $daym2m = 0;
        if ($data['yestoday']) {
            $daym2m = ($data['today'] - $data['yestoday']) / $data['yestoday'];
        }
        $data['daym2m'] = $daym2m;

        $month = getMonth();
        $data['month'] = $this->analyticsOrder->getOrderNum($month['start'], $month['end'], $order_state_ids, $order_is_paids);

        return $data;
    }


    public function getOrderAmount($request)
    {
        //统计没有取消的订单
        $order_state_id = [
            StateCode::ORDER_STATE_WAIT_PAY,
            StateCode::ORDER_STATE_WAIT_PAID,
            StateCode::ORDER_STATE_WAIT_REVIEW,
            StateCode::ORDER_STATE_WAIT_FINANCE_REVIEW,
            StateCode::ORDER_STATE_PICKING,
            StateCode::ORDER_STATE_WAIT_SHIPPING,
            StateCode::ORDER_STATE_SHIPPED,
            StateCode::ORDER_STATE_RECEIVED,
            StateCode::ORDER_STATE_FINISH,
            StateCode::ORDER_STATE_SELF_PICKUP
        ];
        $request['order_state_id'] = $order_state_id;

        $request['order_is_paid'] = [
            StateCode::ORDER_PAID_STATE_PART,
            StateCode::ORDER_PAID_STATE_YES
        ];

        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');

        // 获取当前周期内数据
        $current = $this->analyticsOrder->getOrderAmount($request);
        $data['current'] = $current;
        $data['pre'] = 0;
        $data['daym2m'] = 0;
        // 获取上个周期的数据
        if ($stime && $etime) {
            // 计算上个周期的时间范围
            $pre_stime = $stime - ($etime - $stime);
            $request['stime'] = $pre_stime;
            $request['etime'] = $stime;
            $pre_reg_num = $this->analyticsOrder->getOrderAmount($request);
            if ($pre_reg_num) {
                $data['pre'] = $pre_reg_num;
                $daym2m = (($current - $pre_reg_num) / $pre_reg_num);
                $data['daym2m'] = $daym2m;
            }
        }

        return $data;
    }


    //运营首页-面板数据
    public function getDashboardTimeLine($request)
    {
        $dashboard = [];
        $stime = $request->input('stime', 0);
        $etime = $request->input('etime', 0);

        // 设置响应数据
        $dashboard['order_time_line'] = $this->analyticsOrder->getOrderTimeLine($stime, $etime);
        $dashboard['user_time_line'] = $this->analyticsUser->getUserTimeLine($stime, $etime);
        $dashboard['pt_time_line'] = $this->analyticsProduct->getProductTimeLine($stime, $etime);
        $dashboard['pay_time_line'] = $this->analyticsOrder->getPayTimeLine($stime, $etime);

        return $dashboard;
    }


    public function getOrderNumDate($days)
    {
        $data = $this->analyticsOrder->getOrderNumDate($days);

        return $data;
    }


    public function getSaleOrderAmount($request)
    {
        $stime = $request->input('stime', 0);
        $etime = $request->input('etime', 0);
        $data = $this->analyticsOrder->getSaleOrderAmount($stime, $etime);

        return $data;
    }


    public function getCustomerTimeline($days)
    {
        $result = $this->analyticsOrder->getOrderNumDate($days);

        return $result;
    }

    public function getOrderCustomerNumTimeline($request)
    {
        // 获取请求中的时间参数
        $stime = $request->input('stime', 0);
        $etime = $request->input('etime', 0);
        $result = $this->analyticsOrder->getOrderCustomerNumTimeline($stime, $etime);

        return $result;
    }


    public function getOrderNumTimeline($request)
    {
        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');
        $result = $this->analyticsOrder->getOrderTimeLine($stime, $etime);

        return $result;
    }

    public function getOrderItemNumTimeLine($request)
    {

        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');
        $result = $this->analyticsOrder->getOrderItemNumTimeLine($stime, $etime);

        return $result;
    }

    public function getOrderItemNum($request)
    {
        $data = [
            'pre' => 0,
            'daym2m' => 0
        ];

        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');
        $data['current'] = $current = $this->analyticsOrder->getOrderItemNum($request);

        // 获取上个周期的数据
        if ($stime && $etime) {
            // 计算上个周期的时间范围
            $pre_stime = $stime - ($etime - $stime);
            $pre_etime = $stime;
            $request['stime'] = $pre_stime;
            $request['etime'] = $pre_etime;
            $pre_reg_num = $this->analyticsOrder->getOrderItemNum($request);
            if ($pre_reg_num) {
                $data['pre'] = $pre_reg_num;
                $daym2m = (($current - $pre_reg_num) / $pre_reg_num);
                $data['daym2m'] = $daym2m;
            }
        }

        return $data;
    }


    public function listOrderItemNum($request)
    {
        $data = $this->analyticsOrder->listOrderItemNum($request);

        return $data;
    }

}

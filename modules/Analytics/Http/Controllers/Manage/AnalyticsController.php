<?php

namespace Modules\Analytics\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Analytics\Services\AnalyticsOrderService;
use Modules\Analytics\Services\AnalyticsProductService;
use Modules\Analytics\Services\AnalyticsSysService;
use Modules\Analytics\Services\AnalyticsTradeService;
use Modules\Analytics\Services\AnalyticsUserService;

class AnalyticsController extends BaseController
{
    private $analyticsOrderService;
    private $analyticsTradeService;
    private $analyticsSysService;
    private $analyticsUserService;
    private $analyticsProductService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        AnalyticsOrderService   $analyticsOrderService,
        AnalyticsTradeService   $analyticsTradeService,
        AnalyticsSysService     $analyticsSysService,
        AnalyticsUserService    $analyticsUserService,
        AnalyticsProductService $analyticsProductService
    )
    {
        $this->analyticsOrderService = $analyticsOrderService;
        $this->analyticsTradeService = $analyticsTradeService;
        $this->analyticsSysService = $analyticsSysService;
        $this->analyticsUserService = $analyticsUserService;
        $this->analyticsProductService = $analyticsProductService;
    }

    //获取销售额
    public function getSalesAmount(Request $request)
    {
        $data = $this->analyticsTradeService->getSalesAmount($request);

        return Respond::success($data);
    }

    //获取用户访问量
    public function getVisitor()
    {
        $data = $this->analyticsSysService->getVisitor();

        return Respond::success($data);
    }

    //获取新增用户
    public function getRegUser()
    {
        $data = $this->analyticsUserService->getRegUser();

        return Respond::success($data);
    }

    //仪表板看板柱形图数据
    public function getDashboardTimeLine(Request $request)
    {
        $data = $this->analyticsOrderService->getDashboardTimeLine($request);

        return Respond::success($data);
    }

    //时间用户统计
    public function getUserTimeLine(Request $request)
    {
        $stime = $request->get('stime', 0);
        $etime = $request->get('etime', 0);
        $data = $this->analyticsUserService->getUserTimeLine($stime, $etime);

        return Respond::success($data);
    }

    //用户统计
    public function getUserNum(Request $request)
    {
        $data = $this->analyticsUserService->getUserNum($request);

        return Respond::success($data);
    }

    public function getAccessNum(Request $request)
    {
        $data = $this->analyticsSysService->getAccessNum($request);

        return Respond::success($data);
    }

    public function getAccessVisitorTimeLine(Request $request)
    {
        $data = $this->analyticsSysService->getAccessVisitorTimeLine($request);

        return Respond::success($data);
    }


    public function getAccessVisitorNum(Request $request)
    {
        $data = $this->analyticsSysService->getAccessVisitorNum($request);

        return Respond::success($data);
    }


    //获取订单量
    public function getOrderNum()
    {
        $data = $this->analyticsOrderService->getOrderNum();

        return Respond::success($data);
    }

    public function getOrderAmount(Request $request)
    {
        $data = $this->analyticsOrderService->getOrderAmount($request);

        return Respond::success($data);
    }


    //订单销售金额对比图
    public function getSaleOrderAmount(Request $request)
    {
        $data = $this->analyticsOrderService->getSaleOrderAmount($request);

        return Respond::success($data);
    }


    //消费客户统计
    public function getCustomerTimeline(Request $request)
    {
        $days = $request->input('days');
        $data = $this->analyticsOrderService->getCustomerTimeline($days);

        return Respond::success($data);
    }


    public function getOrderNumToday(Request $request)
    {
        $data = $this->analyticsOrderService->getOrderNum();

        return Respond::success($data);
    }


    public function getOrderCustomerNumTimeline(Request $request)
    {
        $data = $this->analyticsOrderService->getOrderCustomerNumTimeline($request);

        return Respond::success($data);
    }


    public function getOrderNumTimeline(Request $request)
    {
        $data = $this->analyticsOrderService->getOrderNumTimeline($request);

        return Respond::success($data);
    }

    public function getOrderItemNumTimeLine(Request $request)
    {
        $data = $this->analyticsOrderService->getOrderItemNumTimeLine($request);

        return Respond::success($data);
    }

    public function getProductNum(Request $request)
    {
        $data = $this->analyticsProductService->getProductNum($request);

        return Respond::success($data);
    }

    public function getAccessItemNum(Request $request)
    {
        $data = $this->analyticsSysService->getAccessItemNum($request);

        return Respond::success($data);
    }

    public function getAccessItemUserNum(Request $request)
    {
        $data = $this->analyticsSysService->getAccessItemUserNum($request);

        return Respond::success($data);
    }

    public function getOrderItemNum(Request $request)
    {
        $data = $this->analyticsOrderService->getOrderItemNum($request);

        return Respond::success($data);
    }

    public function listOrderItemNum(Request $request)
    {
        $data = $this->analyticsOrderService->listOrderItemNum($request);

        return Respond::success($data);
    }

    public function getAccessItemUserTimeLine(Request $request)
    {
        $data = $this->analyticsSysService->getAccessItemUserTimeLine($request);

        return Respond::success($data);
    }

    public function listAccessItem(Request $request)
    {
        $data = $this->analyticsSysService->listAccessItem($request);

        return Respond::success($data);
    }

    public function getAccessItemTimeLine(Request $request)
    {
        $data = $this->analyticsSysService->getAccessItemTimeLine($request);

        return Respond::success($data);
    }

}

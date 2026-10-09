<?php

namespace Modules\Analytics\Services;

use App\Exceptions\ErrorException;
use Modules\Analytics\Repositories\Models\AnalyticsProduct;

/**
 * Class AnalyticsProductService.
 *
 * @package Modules\Analytics\Services
 */
class AnalyticsProductService
{
    private $analyticsProduct;

    public function __construct(AnalyticsProduct $analyticsProduct)
    {
        $this->analyticsProduct = $analyticsProduct;
    }


    public function getProductNum($request)
    {
        $data = [
            'pre' => 0,
            'daym2m' => 0
        ];

        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');
        $data['current'] = $current = $this->analyticsProduct->getProductNum($stime, $etime);

        // 获取上个周期的数据
        if ($stime && $etime) {
            // 计算上个周期的时间范围
            $pre_stime = $stime - ($etime - $stime);
            $pre_etime = $stime;
            $pre_reg_num = $this->analyticsProduct->getProductNum($pre_stime, $pre_etime);
            if ($pre_reg_num) {
                $data['pre'] = $pre_reg_num;
                $daym2m = (($current - $pre_reg_num) / $pre_reg_num);
                $data['daym2m'] = $daym2m;
            }
        }

        return $data;
    }


}

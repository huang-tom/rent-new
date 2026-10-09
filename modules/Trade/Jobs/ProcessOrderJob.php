<?php

namespace Modules\Trade\Jobs;

use App\Support\LevelCode;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Pay\Services\UserResourceService;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;

class ProcessOrderJob implements ShouldQueue
{
    public $data;
    protected $configBaseRepository;
    protected $userResourceService;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle(ConfigBaseRepository $configBaseRepository, UserResourceService $userResourceService)
    {
        $this->configBaseRepository = $configBaseRepository;
        $this->userResourceService = $userResourceService;
        $data = $this->data;

        if ($data['order_id']) {
            $order_info = $data['order_info'];
            $exp_consume_rate = $this->configBaseRepository->getConfig('exp_consume_rate'); //经验值比例
            $exp_consume_max = $this->configBaseRepository->getConfig('exp_consume_max'); //单笔允许最大值
            $order_exp = ceil($order_info['order_payment_amount'] * $exp_consume_rate);
            $user_exp = min($order_exp, $exp_consume_max);
            Log::info("订单经验值", ['user_exp' => $user_exp]);

            $experience_row = [
                'user_id' => $order_info['user_id'],
                'exp' => $user_exp,
                'exp_type_id' => LevelCode::EXP_TYPE_CONSUME,
                'desc' => __('用户消费') . $order_info['order_id']
            ];
            $this->userResourceService->experience($experience_row);
        }

        Log::info("订单商品信息处理完成", ['order_id' => $data['order_id']]);
    }

}

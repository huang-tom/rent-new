<?php

namespace Modules\Pay\Services;

use App\Support\StateCode;
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Models\User;
use Modules\Pay\Repositories\Contracts\ConsumeDepositRepository;
use App\Exceptions\ErrorException;
use Modules\Pay\Repositories\Contracts\ConsumeRecordRepository;
use Modules\Pay\Repositories\Contracts\ConsumeTradeRepository;
use Modules\Pay\Repositories\Contracts\UserResourceRepository;
use Modules\Sys\Repositories\Models\LogAction;

/**
 * Class ConsumeDepositService.
 *
 * @package Modules\Pay\Services
 */
class ConsumeDepositService extends BaseService
{
    private $consumeTradeRepository;
    private $userResourceRepository;
    private $consumeRecordRepository;
    private $consumeTradeService;

    public function __construct(
        ConsumeDepositRepository $consumeDepositRepository,
        ConsumeTradeRepository   $consumeTradeRepository,
        UserResourceRepository   $userResourceRepository,
        ConsumeRecordRepository  $consumeRecordRepository,

        ConsumeTradeService      $consumeTradeService
    )
    {
        $this->repository = $consumeDepositRepository;
        $this->consumeTradeRepository = $consumeTradeRepository;
        $this->userResourceRepository = $userResourceRepository;
        $this->consumeRecordRepository = $consumeRecordRepository;
        $this->consumeTradeService = $consumeTradeService;
    }


    // ProcessDeposit 新增
    public function processDeposit($request)
    {
        //todo 获取充值记录
        $deposit = $this->repository->findOne([
            'deposit_no' => $request['deposit_no'],
            'deposit_trade_no' => $request['deposit_trade_no'],
        ]);

        DB::beginTransaction();

        if (empty($deposit)) {
            $add_row = $request;
            if (!isset($add_row['deposit_no'])) {
                $add_row['deposit_no'] = $request['order_id'];
            }
            $result = $this->repository->add($add_row);
            if ($result) {
                $last_insert_id = $result->getKey();
                $deposit = $this->repository->getOne($last_insert_id);
            } else {
                throw new ErrorException('充值记录增加失败');
            }
        }

        if ($deposit['deposit_state'] == 0) {
            //todo 获取交易表记录
            $trade_rows = $this->consumeTradeRepository->find([['order_id', 'IN', explode(',', $deposit['order_id'])]]);
            if (empty($trade_rows)) {
                throw new ErrorException('交易订单获取失败');
            }
            $trade = current($trade_rows);

            //todo 处理用户账户增加充值额度
            $resource_flag = $this->userResourceRepository->incrementFieldByIds([$trade['buyer_id']], 'user_money', $deposit['deposit_total_fee']);
            if (!$resource_flag) {
                throw new ErrorException('用户充值失败');
            }

            //todo 写入充值流水
            $record_flag = $this->consumeRecordRepository->addConsumeRecord($trade['buyer_id'], $trade, $deposit);
            if (!$record_flag) {
                throw new ErrorException('充值流水写入失败');
            }

            //todo 修改充值成功状态
            $deposit_result = $this->repository->edit($deposit['deposit_id'], ['deposit_state' => 1]);
            if (!$deposit_result) {
                return new Exception('修改充值状态失败');
            }

            //todo 处理订单支付结果
            $pay_info = [
                'payment_met_id' => StateCode::PAYMENT_MET_MONEY,
                'payment_channel_id' => $deposit['payment_channel_id'],
                'payment_type_id' => $deposit['deposit_payment_type'],
                'pm_money' => $deposit['deposit_total_fee'],
            ];

            $result = $this->consumeTradeService->processPay($deposit['order_id'], $pay_info);
            if (!$result) {
                return new Exception('处理订单支付结果失败');
            }

        } else {
            // 处理充值已完成状态
            // 只是简单说明本次充值已经操作完成
        }

        DB::commit();
    }


    /**
     * 线下支付
     * @param $request
     * @return Exception|null
     * @throws ErrorException
     */
    public function offlinePay($request)
    {
        $consume_deposit = [
            'deposit_time' => $request->input('deposit_time', 0),
            'deposit_notify_time' => getDateTime(),   //通知时间
            'deposit_trade_no' => $request['deposit_trade_no'], //交易号
            'deposit_total_fee' => $request['deposit_total_fee'],   //交易金额
            'payment_channel_id' => $request['payment_channel_id'],   //支付渠道
            'order_id' => $request['order_id'], //商户网站唯一订单号(DOT):合并支付则为多个订单号, 没有创建联合支付交易号
            'deposit_no' => $request['deposit_trade_no'],
            'deposit_payment_type' => StateCode::PAYMENT_TYPE_OFFLINE
        ];

        $result = $this->processDeposit($consume_deposit);

        return $result;
    }


    /**
     * 收款确认（充值记录列表里的「收款确认」开关）
     *
     * [新增 2026-09-23] 补 POST /manage/pay/consumeDeposit/editReview。
     *
     * 为什么这条是「真缺陷」而不是死声明：
     *   `views/pay/consumeDeposit/index.vue` 的「收款确认」列里有一个
     *   `<el-switch v-model="row.deposit_review" @change="handleState(row)" />`，
     *   它**既没有 v-if="false"、也没有 v-permissions 门槛**（该列不是被隐藏的列），
     *   所以对 admin 一定渲染得出来；一点就 POST 这个接口，而接口此前 404 ——
     *   用户看到的是「操作失败」。同页面的添加/编辑/删除按钮都是 HTML 注释掉的，
     *   只有这个开关是活的（用 reachability_audit.py 逐条判定出来的）。
     *
     * 业务边界（刻意保守，不臆造规则）：
     *   · 只改 deposit_review 这一个标记位，**不碰 deposit_state**，
     *     也不触发加钱 —— 真正入账是 processDeposit() 在支付回调里做的事。
     *     「收款确认」是财务的人工核对标记，把它和入账混在一起会造成重复加钱。
     *   · 已作废（deposit_enable = 0）的充值单拒绝确认。
     *   · 这是资金相关动作，落一条 sys_log_action 审计日志（本项目自带该表，
     *     但 LogsMiddleware 从未被挂到任何路由上，属于死基础设施，所以这里直接写）。
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     * @throws ErrorException
     */
    public function editReview($request)
    {
        $deposit_id = (int)$request->input('deposit_id', 0);
        if ($deposit_id <= 0) {
            throw new ErrorException(__('请选择充值记录'));
        }

        $row = $this->repository->getOne($deposit_id);
        if (empty($row)) {
            throw new ErrorException(__('充值记录不存在'));
        }

        if (empty($row['deposit_enable'])) {
            throw new ErrorException(__('该充值记录已作废，不能确认收款'));
        }

        $review = (int)$request->boolean('deposit_review');
        $old_review = (int)$row['deposit_review'];

        // 状态没变就直接返回，不写库、不记日志（开关误触不该产生审计噪音）
        if ($old_review === $review) {
            return [
                'deposit_id'      => $deposit_id,
                'deposit_review'  => $review,
                'changed'         => false,
            ];
        }

        $flag = $this->repository->edit($deposit_id, ['deposit_review' => $review]);
        if (!$flag) {
            throw new ErrorException(__('收款确认失败'));
        }

        $this->writeReviewAuditLog($request, $deposit_id, $old_review, $review);

        return [
            'deposit_id'      => $deposit_id,
            'deposit_review'  => $review,
            'changed'         => true,
        ];
    }


    /**
     * 写一条收款确认的审计日志
     *
     * ⚠️ 审计日志属于「旁路」，任何异常都必须吞掉 ——
     *    否则日志表结构一变（比如 log_date 不接受某格式）就把资金操作一起搞挂。
     *
     * @param \Illuminate\Http\Request $request
     * @param int $deposit_id
     * @param int $old_review
     * @param int $new_review
     * @return void
     */
    private function writeReviewAuditLog($request, $deposit_id, $old_review, $new_review)
    {
        try {
            $user = User::getUser();

            LogAction::create([
                'user_id'        => $user->user_id ?? 0,
                'user_account'   => $user->user_account ?? '',
                'user_name'      => $user->user_nickname ?? '',
                'log_name'       => __('充值收款确认'),
                'action_id'      => $deposit_id,
                'action_type_id' => 0,
                'log_url'        => $request->path(),
                'log_method'     => $request->method(),
                'log_param'      => [
                    'deposit_id'          => $deposit_id,
                    'deposit_review_from' => $old_review,
                    'deposit_review_to'   => $new_review,
                ],
                'log_ip'         => $request->ip(),
                'log_date'       => date('Y-m-d'),
                'log_time'       => time(),
            ]);
        } catch (\Throwable $e) {
            // 审计失败不影响业务，但要在日志里留下痕迹
            app('log')->warning('写入充值收款确认审计日志失败: ' . $e->getMessage());
        }
    }

}

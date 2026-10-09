<?php

namespace Modules\Trade\Services;

use App\Exceptions\ErrorException;
use Kuteshop\Core\Service\BaseService;
use Modules\Trade\Repositories\Contracts\OrderInvoiceRepository;
use Modules\Trade\Repositories\Models\OrderBase;
use Modules\Trade\Repositories\Models\OrderInvoice;

/**
 * Class OrderInvoiceService.
 *
 * @package Modules\Trade\Services
 */
class OrderInvoiceService extends BaseService
{

    public function __construct(OrderInvoiceRepository $orderInvoiceRepository)
    {
        $this->repository = $orderInvoiceRepository;
    }


    /**
     * 发票可写字段白名单
     *
     * ⚠️ 刻意不包含 order_invoice_id / invoice_datetime / invoice_cancel：
     *    主键与作废标记不能由请求体改写。
     *    invoice_img 由前端表单的 invoice_url（电子发票链接）映射过来。
     *
     * @param \Illuminate\Http\Request $request
     * @param array $defaults
     * @return array
     */
    private function formatInvoiceRow($request, array $defaults = [])
    {
        $str_fields = [
            'invoice_title', 'invoice_content', 'invoice_company_code',
            'invoice_address', 'invoice_phone', 'invoice_bankname', 'invoice_bankaccount',
            'invoice_contact_name', 'invoice_contact_area', 'invoice_contact_address',
            'user_intl', 'user_mobile', 'user_email',
        ];

        $row = [];
        foreach ($str_fields as $field) {
            if ($request->has($field)) {
                $row[$field] = (string)$request->input($field, '');
            }
        }

        // 电子发票链接 → 库里叫 invoice_img
        if ($request->has('invoice_url')) {
            $row['invoice_img'] = (string)$request->input('invoice_url', '');
        }

        if ($request->has('store_id')) {
            $row['store_id'] = (int)$request->input('store_id', 0);
        }
        if ($request->has('user_id')) {
            $row['user_id'] = (int)$request->input('user_id', 0);
        }
        if ($request->has('invoice_type')) {
            $row['invoice_type'] = (int)$request->input('invoice_type', 0);
        }
        if ($request->has('invoice_amount')) {
            // 金额列是 decimal(16,6) unsigned，负数会被 MySQL 拒绝（严格模式直接报 SQL 错），
            // 先在这里挡一次，返回人话
            $amount = (float)$request->input('invoice_amount', 0);
            if ($amount < 0) {
                throw new ErrorException(__('开票金额不能为负数'));
            }
            $row['invoice_amount'] = $amount;
        }
        if ($request->has('invoice_is_company')) {
            $row['invoice_is_company'] = $request->boolean('invoice_is_company');
        }
        if ($request->has('invoice_is_electronic')) {
            $row['invoice_is_electronic'] = $request->boolean('invoice_is_electronic');
        }
        if ($request->has('invoice_status')) {
            $row['invoice_status'] = $request->boolean('invoice_status');
        }

        return array_merge($row, $defaults);
    }


    /**
     * 新增发票
     *
     * [新增 2026-09-22] 补齐 POST /manage/trade/orderInvoice/add。
     * 前端 api/trade/orderInvoice.ts 的 doAdd 指向它，订单发票编辑弹窗
     * （views/trade/orderInvoice/components/OrderInvoiceEdit.vue）在非编辑态会调用。
     * 注意：该弹窗的入口按钮目前被 v-if="false" 隐藏（原厂刻意关闭），
     *      所以这是"契约补齐"而非"新开能力"—— 一旦按钮放开即可正常工作。
     *
     * @param $request
     * @return array
     * @throws ErrorException
     */
    public function addInvoice($request)
    {
        $order_id = trim((string)$request->input('order_id', ''));
        if ($order_id === '') {
            throw new ErrorException(__('请输入订单编号'));
        }

        $order = OrderBase::where('order_id', $order_id)
            ->first(['order_id', 'user_id', 'order_payment_amount']);
        if (empty($order)) {
            throw new ErrorException(__('订单不存在'));
        }

        // 同一订单不允许重复开票：否则一张订单会挂出多张有效发票
        if (OrderInvoice::where('order_id', $order_id)->where('invoice_cancel', false)->exists()) {
            throw new ErrorException(__('该订单已存在发票，请勿重复开票'));
        }

        $user_id = (int)$request->input('user_id', 0);
        if ($user_id <= 0) {
            $user_id = (int)$order->user_id;
        }

        $row = $this->formatInvoiceRow($request, [
            'order_id' => $order_id,
            'user_id' => $user_id,
            'invoice_amount' => $request->has('invoice_amount')
                ? $request->input('invoice_amount')
                : $order->order_payment_amount,
            'invoice_datetime' => time(),
        ]);

        $invoice = OrderInvoice::create($row);
        if (!$invoice) {
            throw new ErrorException(__('操作失败'));
        }

        return $invoice->toArray();
    }


    /**
     * 修改发票
     *
     * [新增 2026-09-22] 补齐 POST /manage/trade/orderInvoice/edit。
     *
     * ⚠️ 已作废（invoice_cancel = 1）的发票不允许再改，否则等于篡改历史凭证。
     *    注意本方法**不改 order_id**：换订单应该删掉重开，而不是把发票挪到别的订单上。
     *
     * @param $request
     * @return array
     * @throws ErrorException
     */
    public function editInvoice($request)
    {
        $order_invoice_id = (int)$request->input('order_invoice_id', 0);
        if ($order_invoice_id <= 0) {
            throw new ErrorException(__('数据有误'));
        }

        $invoice = OrderInvoice::find($order_invoice_id);
        if (empty($invoice)) {
            throw new ErrorException(__('发票不存在'));
        }
        if ($invoice->invoice_cancel) {
            throw new ErrorException(__('已作废的发票不允许修改'));
        }

        $row = $this->formatInvoiceRow($request);
        // 订单归属不可通过编辑接口变更
        unset($row['order_id']);

        if (empty($row)) {
            throw new ErrorException(__('没有需要更新的内容'));
        }

        $invoice->fill($row)->save();

        return $invoice->toArray();
    }


    /**
     * 删除发票（支持单个 / 批量）
     *
     * [新增 2026-09-22] 补齐 POST /manage/trade/orderInvoice/remove。
     * 前端「单个删除」与「批量删除」用的是同一条路由
     * （api/trade/orderInvoice.ts 里 doRemove 与 doRemoveBatch 的 url 相同），
     * 批量时 order_invoice_id 是逗号拼接串，所以入参三种形态都要兼容。
     *
     * @param $request
     * @return bool
     * @throws ErrorException
     */
    public function removeInvoice($request)
    {
        $raw = $request->input('order_invoice_id');
        $ids = is_array($raw) ? $raw : explode(',', (string)$raw);
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            throw new ErrorException(__('请选择要删除的数据'));
        }

        $deleted = OrderInvoice::whereIn('order_invoice_id', $ids)->delete();
        if ($deleted > 0) {
            return true;
        }

        throw new ErrorException(__('删除失败'));
    }

}

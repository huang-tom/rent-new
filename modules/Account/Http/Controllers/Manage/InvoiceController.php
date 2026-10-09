<?php

namespace Modules\Account\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Criteria\UserInvoiceCriteria;
use Modules\Account\Repositories\Validators\UserInvoiceValidator;
use Modules\Account\Services\UserInvoiceService;

/**
 * Class InvoiceController.
 *
 * [新增 2026-09-23] 管理端会员发票抬头控制器。
 *
 * 为什么需要它：
 *   「订单管理 → 订单发票 → 编辑」里的「发票抬头」是下拉选择，
 *   数据源就是本接口（views/trade/orderInvoice/components/OrderInvoiceEdit.vue:256
 *   调 getList({user_id, size:500}) 取 data.items）。没有它，抬头只能手打，
 *   既选不到会员已保存的抬头，也没有"开票信息"的一致性。
 *
 * 与 Front 版的差异：同 DeliveryAddressController —— 那个是会员管自己的抬头（user_id 取登录态），
 * 这个是管理员按 user_id 查会员的抬头，所以 user_id 从请求取，不覆盖。
 *
 * @package Modules\Account\Http\Controllers\Manage
 */
class InvoiceController extends BaseController
{

    private $userInvoiceService;
    private $userInvoiceValidator;

    public function __construct(
        UserInvoiceService   $userInvoiceService,
        UserInvoiceValidator $userInvoiceValidator
    )
    {
        $this->userInvoiceService = $userInvoiceService;
        $this->userInvoiceValidator = $userInvoiceValidator;
    }


    /**
     * 会员发票抬头列表（按 user_id 过滤）
     */
    public function list(Request $request)
    {
        $data = $this->userInvoiceService->list($request, new UserInvoiceCriteria($request));

        return Respond::success($data);
    }


    /**
     * 格式化请求数组
     * @param $request
     * @return array
     */
    public function formatRequest($request)
    {
        return [
            'user_id' => $request->input('user_id', 0), //所属用户
            'invoice_title' => $request->input('invoice_title', ''), //发票抬头
            'invoice_company_code' => $request->input('invoice_company_code', ''), //纳税人识别号
            'invoice_content' => $request->input('invoice_content', ''), //发票内容
            'invoice_is_company' => $request->boolean('invoice_is_company'), //公司开票(BOOL):0-个人;1-公司
            'invoice_is_electronic' => $request->boolean('invoice_is_electronic'), //电子发票(ENUM):0-纸质;1-电子
            'invoice_type' => $request->input('invoice_type', 1), //发票类型(ENUM):1-普通;2-增值税专用
            'invoice_address' => $request->input('invoice_address', ''), //单位地址
            'invoice_phone' => $request->input('invoice_phone', ''), //单位电话
            'invoice_bankname' => $request->input('invoice_bankname', ''), //开户银行
            'invoice_bankaccount' => $request->input('invoice_bankaccount', ''), //银行账号
            'invoice_contact_mobile' => $request->input('invoice_contact_mobile', ''), //收票人手机
            'invoice_contact_email' => $request->input('invoice_contact_email', ''), //收票人邮箱
            'invoice_is_default' => $request->input('invoice_is_default', 0), //是否默认
            'invoice_contact_name' => $request->input('invoice_contact_name', ''), //收票人
            'invoice_contact_area' => $request->input('invoice_contact_area', ''), //收票人地区
            'invoice_contact_address' => $request->input('invoice_contact_address', ''), //收票详细地址
        ];
    }


    /**
     * 新增会员发票抬头
     */
    public function add(Request $request)
    {
        $this->userInvoiceValidator->with($request->all())->passesOrFail('create');

        if (!$request->input('user_id')) {
            return Respond::error(__('请先选择所属会员'));
        }

        $add_row = $this->formatRequest($request);
        $add_row['invoice_datetime'] = getDateTime();

        $data = $this->userInvoiceService->add($add_row);

        return Respond::success($data);
    }


    /**
     * 修改会员发票抬头
     */
    public function edit(Request $request)
    {
        $user_invoice_id = (int)$request->input('user_invoice_id', 0);
        if (!$user_invoice_id) {
            return Respond::error(__('发票编号不能为空'));
        }

        $row = $this->userInvoiceService->get($user_invoice_id);
        if (empty($row)) {
            return Respond::error(__('发票不存在'));
        }

        $this->userInvoiceValidator->with($request->all())->passesOrFail('update');

        // 归属沿用原记录，避免前端漏传 user_id 时改挂到 0 号用户
        $request['user_id'] = $row['user_id'];
        $data = $this->userInvoiceService->edit($user_invoice_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除会员发票抬头
     */
    public function remove(Request $request)
    {
        $user_invoice_id = (int)$request->input('user_invoice_id', 0);
        if (!$user_invoice_id) {
            return Respond::error(__('发票编号不能为空'));
        }

        $data = $this->userInvoiceService->remove($user_invoice_id);

        return Respond::success($data);
    }

}

<?php

namespace Modules\Account\Http\Controllers\Front;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Criteria\UserInvoiceCriteria;
use Modules\Account\Repositories\Validators\UserInvoiceValidator;
use Modules\Account\Services\UserInvoiceService;
use Modules\Sys\Services\ConfigBaseService;

class InvoiceController extends BaseController
{

    private $userId;
    private $userInvoiceService;
    private $userInvoiceValidator;
    private $configBaseService;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        UserInvoiceService   $userInvoiceService,
        UserInvoiceValidator $userInvoiceValidator,
        ConfigBaseService    $configBaseService
    )
    {
        $this->userId = checkLoginUserId();

        $this->userInvoiceService = $userInvoiceService;
        $this->userInvoiceValidator = $userInvoiceValidator;
        $this->configBaseService = $configBaseService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $request['user_id'] = $this->userId;
        $data = $this->userInvoiceService->list($request, new UserInvoiceCriteria($request));

        return Respond::success($data);
    }


    /**
     * 获取数据
     */
    public function get(Request $request)
    {
        $data = $this->userInvoiceService->get($request['user_invoice_id']);
        checkDataRights($this->userId, $data);

        return Respond::success($data);
    }


    /**
     * 格式化请求数组
     * @param $request
     * @return array
     */
    public function formatRequest($request)
    {
        $data = [
            'user_id' => $request->input('user_id', 0), //所属用户
            'invoice_title' => $request->input('invoice_title', ''), //发票抬头
            'invoice_company_code' => $request->input('invoice_company_code', '+86'), //纳税人识别号
            'invoice_content' => $request->input('invoice_content', ''), //发票内容
            'invoice_is_company' => $request->boolean('invoice_is_company'), //公司开票(BOOL):0-个人;1-公司
            'invoice_is_electronic' => $request->boolean('invoice_is_electronic'), //电子发票(ENUM):0-纸质发票;1-电子发票
            'invoice_type' => $request->input('invoice_type', 1), //发票类型(ENUM):1-普通发票;2-增值税专用发票
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

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->userInvoiceValidator->with($request->all())->passesOrFail('create');

        $request['user_id'] = $this->userId;
        $add_row = $this->formatRequest($request);
        $add_row['invoice_datetime'] = getDateTime();

        $data = $this->userInvoiceService->add($add_row);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $user_invoice_id = $request['user_invoice_id'];
        $row = $this->userInvoiceService->get($user_invoice_id);
        checkDataRights($this->userId, $row);

        $this->userInvoiceValidator->with($request->all())->passesOrFail('update');

        $request['user_id'] = $this->userId;
        $data = $this->userInvoiceService->edit($user_invoice_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $row = $this->userInvoiceService->get($request['user_invoice_id']);
        checkDataRights($this->userId, $row);

        $data = $this->userInvoiceService->remove($request['user_invoice_id']);

        return Respond::success($data);
    }


    /**
     * 发票说明
     */
    public function getInvoiceTips()
    {
        $invoice_tips = $this->configBaseService->getConfig('invoice_tips');
        return Respond::ok($invoice_tips);
    }

}

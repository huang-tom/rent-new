<?php

namespace Modules\Pay\Http\Controllers\Front;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Account\Repositories\Models\User;
use Modules\Pay\Repositories\Criteria\BaseBankCriteria;
use Modules\Pay\Repositories\Criteria\UserBankCardCriteria;
use Modules\Pay\Services\BaseBankService;
use Modules\Pay\Services\UserBankCardService;

class UserBankController extends BaseController
{
    private $userBankCardService;
    private $baseBankService;
    private $userId;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserBankCardService $userBankCardService, BaseBankService $baseBankService)
    {
        $this->userBankCardService = $userBankCardService;
        $this->baseBankService = $baseBankService;

        $this->userId = User::getUserId();
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $base_list = $this->baseBankService->list($request, new BaseBankCriteria($request));
        $data['bank_list'] = $base_list['data'];

        $user_bank_list = $this->userBankCardService->list($request, new UserBankCardCriteria($request));
        $data['user_bank_list'] = $user_bank_list['data'];

        return Respond::success($data);
    }


    /**
     * 获取银行卡信息
     */
    public function get(Request $request)
    {
        $user_bank_id = $request->get('user_bank_id', -1);
        $data = $this->userBankCardService->get($user_bank_id);

        return Respond::success($data);
    }


    /**
     * 新增/修改银行卡信息
     */
    public function addOrEditUserBank(Request $request)
    {
        $user_bank_row = [
            'user_id' => $this->userId,
            'bank_id' => $request->input('bank_id', 0),  //别名
            'bank_name' => $request->input('bank_name', ''),  //银行名称
            'user_bank_card_address' => $request->input('user_bank_card_address', ''), //开户支行名称
            'user_bank_card_code' => $request['user_bank_card_code'],       //银行卡卡号
            'user_bank_card_name' => $request->input('user_bank_card_name', ''),  //卡号账户名称
            'user_bank_card_mobile' => $request->input('user_bank_card_mobile', ''), //银行预留手机号
            'user_intl' => $request->input('currency_id', '86'),       //国家区号
            'user_bank_default' => 0,
            'user_bank_begin_date' => 0,
            'user_bank_amount_money' => 0
        ];
        $user_bank_id = $request->get('user_bank_id', -1);
        if ($user_bank_id) {
            $data = $this->userBankCardService->edit($user_bank_id, $user_bank_row);
        } else {
            $data = $this->userBankCardService->add($user_bank_row);
        }

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $user_bank_id = $request->get('user_bank_id', -1);
        $data = $this->userBankCardService->remove($user_bank_id);

        return Respond::success($data);
    }


}

<?php

namespace Modules\Sys\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Repositories\Criteria\CurrencyBaseCriteria;
use Modules\Sys\Repositories\Validators\CurrencyBaseValidator;
use Modules\Sys\Services\CurrencyBaseService;

class CurrencyBaseController extends BaseController
{
    private $currencyBaseService;
    private $currencyBaseValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(CurrencyBaseService $currencyBaseService, CurrencyBaseValidator $currencyBaseValidator)
    {
        $this->currencyBaseService = $currencyBaseService;
        $this->currencyBaseValidator = $currencyBaseValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->currencyBaseService->list($request, new CurrencyBaseCriteria($request));

        return Respond::success($data);
    }


    /**
     * 格式化请求数据
     * @param $request
     * @return array
     */
    public function formatRequest($request)
    {
        $data = [
            'currency_title' => $request->input('currency_title', ''),
            'currency_lang' => $request->input('currency_lang', ''),
            'currency_img' => $request->input('currency_img', ''),
            'currency_symbol_left' => $request->input('currency_symbol_left', ''),
            'currency_symbol_right' => $request->input('currency_symbol_right', ''),
            'currency_decimal_place' => $request->boolean('currency_decimal_place', false),
            'currency_exchange_rate' => $request->input('currency_exchange_rate', 1),
            'currency_status' => $request->boolean('currency_status', false),
            'currency_is_default' => $request->boolean('currency_is_default', false),
            'currency_default_lang' => $request->boolean('currency_default_lang', false),
            'currency_is_standard' => $request->boolean('currency_is_standard', false),
            'currency_sort' => $request->input('currency_sort', 0),
        ];

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->currencyBaseValidator->with($request->all())->passesOrFail('create');
        $data = $this->currencyBaseService->addCurrencyBase($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $currency_id = $request->get('currency_id', -1);
        $this->currencyBaseValidator->setId($currency_id);
        $this->currencyBaseValidator->with($request->all())->passesOrFail('update');
        $data = $this->currencyBaseService->editCurrencyBase($currency_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        if ($currency_id = $request->get('currency_id')) {
            $result = $this->currencyBaseService->editState($currency_id, $request);
            return Respond::success($result);
        } else {
            return Respond::error(__('无效的货币ID'));
        }
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $currency_id = $request->input('currency_id', -1);
        $row = $this->currencyBaseService->get($currency_id);
        if ($row['currency_is_default']) {
            throw new ErrorException('默认币种，不可删除！');
        }

        if ($row['currency_default_lang']) {
            throw new ErrorException('默认语言，不可删除！');
        }

        $this->currencyBaseService->remove($currency_id);

        return Respond::success([]);
    }

}

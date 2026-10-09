<?php

namespace Modules\Pay\Http\Controllers\Manage;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Pay\Repositories\Criteria\ConsumeTradeCriteria;
use Modules\Pay\Services\ConsumeTradeService;

class ConsumeTradeController extends BaseController
{
    private $consumeTradeService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ConsumeTradeService $consumeTradeService)
    {
        $this->consumeTradeService = $consumeTradeService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->consumeTradeService->list($request, new ConsumeTradeCriteria($request));

        return Respond::success($data);
    }

}

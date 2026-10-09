<?php

namespace Modules\O2o\Http\Controllers\Front;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\O2o\Services\ChainBaseService;

class ChainController extends BaseController
{
    private $chainBaseService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ChainBaseService $chainBaseService)
    {
        $this->chainBaseService = $chainBaseService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->chainBaseService->getNearChain($request);

        return Respond::success($data);
    }

}

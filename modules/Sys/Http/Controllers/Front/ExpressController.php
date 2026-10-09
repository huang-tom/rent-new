<?php

namespace Modules\Sys\Http\Controllers\Front;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Repositories\Criteria\ExpressBaseCriteria;
use Modules\Sys\Services\ExpressBaseService;

class ExpressController extends BaseController
{
    private $expressBaseService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ExpressBaseService $expressBaseService)
    {
        $this->expressBaseService = $expressBaseService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $request['express_enable'] = 1;
        $request['size'] = 999;
        $data = $this->expressBaseService->list($request, new ExpressBaseCriteria($request));

        return Respond::success($data['data']);
    }


}

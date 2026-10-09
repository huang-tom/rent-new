<?php

namespace Modules\Sys\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Repositories\Criteria\LogActionCriteria;
use Modules\Sys\Services\LogActionService;

class LogActionController extends BaseController
{
    private $logActionService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(LogActionService $logActionService)
    {
        $this->logActionService = $logActionService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->logActionService->list($request, new LogActionCriteria($request));

        return Respond::success($data);
    }

}

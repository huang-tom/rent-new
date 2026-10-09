<?php

namespace Modules\Sys\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Repositories\Criteria\LogErrorCriteria;
use Modules\Sys\Services\LogErrorService;

class LogErrorController extends BaseController
{
    private $logErrorService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(LogErrorService $logErrorService)
    {
        $this->logErrorService = $logErrorService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->logErrorService->list($request, new LogErrorCriteria($request));

        return Respond::success($data);
    }


}

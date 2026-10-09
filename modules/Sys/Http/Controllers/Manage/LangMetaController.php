<?php

namespace Modules\Sys\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Repositories\Criteria\LangMetaCriteria;
use Modules\Sys\Services\LangMetaService;

class LangMetaController extends BaseController
{
    private $langMetaService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(LangMetaService $langMetaService)
    {
        $this->langMetaService = $langMetaService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->langMetaService->list($request, new LangMetaCriteria($request));

        return Respond::success($data);
    }

}

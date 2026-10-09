<?php

namespace Modules\Sys\Http\Controllers\Front;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
Use Illuminate\Http\Request;
use Modules\Sys\Services\DistrictBaseService;

class DistrictController extends BaseController
{
    private $districtBaseService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(DistrictBaseService $districtBaseService)
    {
        $this->districtBaseService = $districtBaseService;
    }


    /**
     * 列表
     */
    public function tree(Request $request)
    {
        $data = $this->districtBaseService->tree($request);

        return Respond::success($data);
    }

}
